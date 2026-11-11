<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\Requisition;
use App\Models\UploadedDocument;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;
use ZipArchive;

class RequisitionDocumentArchiveController extends Controller
{
    public function download(Request $request, Requisition $requisition): StreamedResponse
    {
        abort_unless($request->user()?->isAdministrator(), 403);

        $documents = $requisition->uploadedDocuments()
            ->orderBy('id')
            ->get(['id', 'requisition_id', 'kind', 'original_name', 'content']);

        $purchaseOrder = $requisition->purchaseOrder()->first();

        if ($purchaseOrder !== null) {
            $documents = $documents->concat(
                $purchaseOrder->uploadedDocuments()
                    ->orderBy('id')
                    ->get(['id', 'purchase_order_id', 'kind', 'original_name', 'content']),
            );
        }

        abort_if($documents->isEmpty(), 404);

        $folderName = str_replace(['\\', '/'], '-', $requisition->folio);
        $folderName = preg_replace('/[<>:"|?*\x00-\x1F]/', '', $folderName);

        if ($folderName === null) {
            throw new RuntimeException('No fue posible preparar el nombre del compendio.');
        }

        $folderName = trim($folderName, ". \t\n\r\0\x0B");
        $folderName = $folderName !== '' ? $folderName : 'requisicion-'.$requisition->getKey();
        $archivePath = $this->createTemporaryArchivePath();

        try {
            $this->createArchive($documents, $archivePath, $folderName);
        } catch (Throwable $exception) {
            if (file_exists($archivePath) && ! unlink($archivePath)) {
                throw new RuntimeException('No fue posible limpiar el compendio temporal.', 0, $exception);
            }

            throw $exception;
        }

        return $this->streamArchive($archivePath, $folderName.'.zip');
    }

    public function downloadRange(Request $request): StreamedResponse
    {
        abort_unless($request->user()?->isAdministrator(), 403);

        $validated = $request->validate([
            'from_date' => ['required', 'date_format:Y-m-d'],
            'to_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:from_date'],
            'group_by' => ['required', 'in:day,week,month,year'],
        ]);
        $archivePath = $this->createTemporaryArchivePath();

        try {
            $documentCount = $this->createDateRangeArchive(
                $archivePath,
                $validated['from_date'],
                $validated['to_date'],
                $validated['group_by'],
            );
            abort_if($documentCount === 0, 404);
        } catch (Throwable $exception) {
            if (file_exists($archivePath) && ! unlink($archivePath)) {
                throw new RuntimeException('No fue posible limpiar el compendio temporal.', 0, $exception);
            }

            throw $exception;
        }

        return $this->streamArchive(
            $archivePath,
            'archivos-'.$validated['from_date'].'-a-'.$validated['to_date'].'.zip',
        );
    }

    /**
     * @param  Collection<int, UploadedDocument>  $documents
     */
    private function createArchive(Collection $documents, string $archivePath, string $folderName): void
    {
        $archive = new ZipArchive;

        if ($archive->open($archivePath, ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('No fue posible crear el compendio de archivos.');
        }

        try {
            $this->addDocumentsToArchive($archive, $documents, $folderName);
        } catch (Throwable $exception) {
            if (! $archive->close()) {
                throw new RuntimeException('No fue posible cerrar el compendio de archivos.', 0, $exception);
            }

            throw $exception;
        }

        if (! $archive->close()) {
            throw new RuntimeException('No fue posible finalizar el compendio de archivos.');
        }
    }

    private function createDateRangeArchive(
        string $archivePath,
        string $fromDate,
        string $toDate,
        string $groupBy,
    ): int {
        $archive = new ZipArchive;

        if ($archive->open($archivePath, ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('No fue posible crear el compendio de archivos.');
        }

        $documentCount = 0;

        try {
            PurchaseOrder::query()
                ->with('requisition:id,folio')
                ->whereDate('order_date', '>=', $fromDate)
                ->whereDate('order_date', '<=', $toDate)
                ->select(['id', 'order_date', 'requisition_id'])
                ->chunkById(50, function (Collection $purchaseOrders) use (
                    $archive,
                    $groupBy,
                    &$documentCount,
                ): void {
                    foreach ($purchaseOrders as $purchaseOrder) {
                        $documents = UploadedDocument::query()
                            ->where(function (Builder $query) use ($purchaseOrder): void {
                                $query->where('purchase_order_id', $purchaseOrder->getKey())
                                    ->orWhere('requisition_id', $purchaseOrder->requisition_id);
                            })
                            ->orderBy('id')
                            ->get(['id', 'requisition_id', 'kind', 'original_name', 'content']);

                        if ($documents->isEmpty()) {
                            continue;
                        }

                        $requisitionFolder = $this->safeFolderName(
                            $purchaseOrder->requisition->folio,
                            'requisicion-'.$purchaseOrder->requisition_id,
                        );
                        $folderName = $this->dateFolderPath($purchaseOrder->order_date, $groupBy)
                            .'/'.$requisitionFolder;

                        $documentCount += $this->addDocumentsToArchive($archive, $documents, $folderName);
                    }
                });
        } catch (Throwable $exception) {
            if (! $archive->close()) {
                throw new RuntimeException('No fue posible cerrar el compendio de archivos.', 0, $exception);
            }

            throw $exception;
        }

        if (! $archive->close()) {
            throw new RuntimeException('No fue posible finalizar el compendio de archivos.');
        }

        return $documentCount;
    }

    private function dateFolderPath(CarbonInterface $orderDate, string $groupBy): string
    {
        $year = $orderDate->format('Y');
        $month = $orderDate->format('m');
        $week = 'semana-'.$orderDate->format('o-W');
        $day = $orderDate->format('Y-m-d');

        return match ($groupBy) {
            'year' => implode('/', [$year, $month, $week, $day]),
            'month' => implode('/', [$year.'-'.$month, $week, $day]),
            'week' => implode('/', [$week, $day]),
            'day' => $day,
            default => throw new RuntimeException('La organización de fechas no es válida.'),
        };
    }

    private function safeFolderName(string $name, string $fallback): string
    {
        $name = str_replace(['\\', '/'], '-', $name);
        $name = preg_replace('/[<>:"|?*\x00-\x1F]/', '', $name);

        if ($name === null) {
            throw new RuntimeException('No fue posible preparar el nombre de una carpeta.');
        }

        $name = trim($name, ". \t\n\r\0\x0B");

        return $name !== '' ? $name : $fallback;
    }

    /**
     * @param  Collection<int, UploadedDocument>  $documents
     */
    private function addDocumentsToArchive(
        ZipArchive $archive,
        Collection $documents,
        string $folderName,
    ): int {
        $documentCount = 0;

        foreach ($documents as $document) {
            $ownerFolder = $document->requisition_id !== null ? 'requisicion' : 'orden-compra';
            $kind = Str::slug($document->kind) ?: 'archivo';
            $originalName = basename(str_replace('\\', '/', $document->original_name));
            $originalName = trim(str_replace("\0", '', $originalName));
            $originalName = in_array($originalName, ['', '.', '..'], true) ? 'archivo' : $originalName;
            $entryName = $folderName.'/'.$ownerFolder.'/'.$kind.'-'.$originalName;

            if (! $archive->addFromString($entryName, (string) $document->content)) {
                throw new RuntimeException('No fue posible agregar un archivo al compendio.');
            }

            $documentCount++;
        }

        return $documentCount;
    }

    private function createTemporaryArchivePath(): string
    {
        $archivePath = tempnam(sys_get_temp_dir(), 'sigaf-');

        if ($archivePath === false) {
            throw new RuntimeException('No fue posible preparar el compendio de archivos.');
        }

        return $archivePath;
    }

    private function streamArchive(string $archivePath, string $fileName): StreamedResponse
    {
        return response()->streamDownload(
            static function () use ($archivePath): void {
                $stream = fopen($archivePath, 'rb');

                if ($stream === false) {
                    if (! unlink($archivePath)) {
                        throw new RuntimeException('No fue posible limpiar el compendio temporal.');
                    }

                    throw new RuntimeException('No fue posible leer el compendio de archivos.');
                }

                try {
                    if (fpassthru($stream) === false) {
                        throw new RuntimeException('No fue posible transmitir el compendio de archivos.');
                    }
                } finally {
                    fclose($stream);

                    if (! unlink($archivePath)) {
                        throw new RuntimeException('No fue posible limpiar el compendio temporal.');
                    }
                }
            },
            $fileName,
            [
                'Content-Type' => 'application/zip',
                'X-Content-Type-Options' => 'nosniff',
            ],
        );
    }
}
