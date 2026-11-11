<?php

namespace App\Http\Controllers;

use App\Models\Requisition;
use App\Models\UploadedDocument;
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
        $archivePath = tempnam(sys_get_temp_dir(), 'sigaf-');

        if ($archivePath === false) {
            throw new RuntimeException('No fue posible preparar el compendio de archivos.');
        }

        try {
            $this->createArchive($documents, $archivePath, $folderName);
        } catch (Throwable $exception) {
            if (! unlink($archivePath)) {
                throw new RuntimeException('No fue posible limpiar el compendio temporal.', 0, $exception);
            }

            throw $exception;
        }

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
            $folderName.'.zip',
            [
                'Content-Type' => 'application/zip',
                'X-Content-Type-Options' => 'nosniff',
            ],
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
            }
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
}
