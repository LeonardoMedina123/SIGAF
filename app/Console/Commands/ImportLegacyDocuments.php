<?php

namespace App\Console\Commands;

use App\Models\PurchaseOrder;
use App\Models\Requisition;
use App\Models\UploadedDocument;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

#[Signature('app:import-legacy-documents')]
#[Description('Importa documentos heredados del almacenamiento local a la base de datos.')]
class ImportLegacyDocuments extends Command
{
    private int $importedCount = 0;

    private int $alreadyStoredCount = 0;

    private int $missingCount = 0;

    public function handle(): int
    {
        Requisition::query()
            ->whereNotNull('pdf_path')
            ->orderBy('id')
            ->chunkById(100, function (Collection $requisitions): void {
                foreach ($requisitions as $requisition) {
                    $this->importDocument($requisition, 'pdf_path', 'requisition_pdf');
                }
            });

        $documentKinds = [
            'pdf_path' => 'purchase_order_pdf',
            'xml_path' => 'purchase_order_xml',
            'invoice_pdf_path' => 'invoice_pdf',
            'invoice_xml_path' => 'invoice_xml',
            'payment_complement_path' => 'payment_complement',
        ];

        PurchaseOrder::query()
            ->where(function (Builder $query) use ($documentKinds): void {
                foreach (array_keys($documentKinds) as $pathColumn) {
                    $query->orWhereNotNull($pathColumn);
                }
            })
            ->orderBy('id')
            ->chunkById(100, function (Collection $purchaseOrders) use ($documentKinds): void {
                foreach ($purchaseOrders as $purchaseOrder) {
                    foreach ($documentKinds as $pathColumn => $kind) {
                        $this->importDocument($purchaseOrder, $pathColumn, $kind);
                    }
                }
            });

        $this->info("Importados: {$this->importedCount}; ya estaban en la base de datos: {$this->alreadyStoredCount}; archivos locales faltantes: {$this->missingCount}.");

        return $this->missingCount > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function importDocument(Requisition|PurchaseOrder $owner, string $pathColumn, string $kind): void
    {
        $legacyPath = $owner->getAttribute($pathColumn);

        if (! is_string($legacyPath) || $legacyPath === '') {
            return;
        }

        $existingDocument = $owner->uploadedDocuments()->where('kind', $kind)->exists();
        if ($existingDocument) {
            $owner->forceFill([$pathColumn => null])->saveOrFail();
            $this->alreadyStoredCount++;

            return;
        }

        $disk = Storage::disk('local');
        if (! $disk->exists($legacyPath)) {
            $this->warn("No se encontró el archivo {$legacyPath} para el documento {$kind} del registro {$owner->getKey()}.");
            $this->missingCount++;

            return;
        }

        DB::transaction(function () use ($disk, $kind, $legacyPath, $owner, $pathColumn): void {
            $mimeType = $disk->mimeType($legacyPath);
            UploadedDocument::replaceContentFor(
                $owner,
                $kind,
                basename($legacyPath),
                is_string($mimeType) ? $mimeType : 'application/octet-stream',
                $disk->get($legacyPath),
            );
            $owner->forceFill([$pathColumn => null])->saveOrFail();
        });

        $this->importedCount++;
    }
}
