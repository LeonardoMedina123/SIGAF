<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ValidatePurchaseOrderFinancialResourcesController extends Controller
{
    public function __invoke(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $financialUser = $request->user();
        abort_unless($financialUser instanceof User && $financialUser->isFinancialsDepartment(), 403);

        DB::transaction(function () use ($financialUser, $purchaseOrder): void {
            $lockedPurchaseOrder = PurchaseOrder::query()
                ->lockForUpdate()
                ->findOrFail($purchaseOrder->id);

            if ($lockedPurchaseOrder->financial_resources_validated_at !== null) {
                return;
            }

            $wasSaved = $lockedPurchaseOrder->forceFill([
                'financial_resources_validated_at' => now(),
                'financial_resources_validated_by' => $financialUser->id,
            ])->save();

            if (! $wasSaved) {
                throw new RuntimeException('No fue posible registrar la validación de recursos financieros.');
            }
        });

        return back()->with(
            'success',
            'Los recursos financieros de la orden '.$purchaseOrder->folio.' quedaron validados.',
        );
    }
}
