<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

class ValidatePurchaseOrderPaymentController extends Controller
{
    public function __invoke(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $administrator = $request->user();
        abort_unless($administrator instanceof User && $administrator->isAdministrator(), 403);

        if ($purchaseOrder->payment_validated_at === null) {
            $wasSaved = $purchaseOrder->forceFill([
                'payment_validated_at' => now(),
                'payment_validated_by' => $administrator->id,
            ])->save();

            if (! $wasSaved) {
                throw new RuntimeException('No fue posible registrar la validación del pago.');
            }
        }

        return back()->with('success', 'El pago de la orden '.$purchaseOrder->folio.' quedó validado.');
    }
}
