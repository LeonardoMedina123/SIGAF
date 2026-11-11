<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\User;
use App\Notifications\UserActivityNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use RuntimeException;

class ValidatePurchaseOrderPaymentController extends Controller
{
    public function __invoke(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $administrator = $request->user();
        abort_unless($administrator instanceof User && $administrator->isAdministrator(), 403);

        DB::transaction(function () use ($purchaseOrder, $administrator): void {
            $lockedPurchaseOrder = PurchaseOrder::query()
                ->lockForUpdate()
                ->findOrFail($purchaseOrder->id);

            if ($lockedPurchaseOrder->payment_validated_at !== null) {
                return;
            }

            $wasSaved = $lockedPurchaseOrder->forceFill([
                'payment_validated_at' => now(),
                'payment_validated_by' => $administrator->id,
            ])->save();

            if (! $wasSaved) {
                throw new RuntimeException('No fue posible registrar la validación del pago.');
            }

            $supplierEmail = $lockedPurchaseOrder->supplier()->value('contact_email');
            $recipients = User::query()
                ->whereKey($lockedPurchaseOrder->requisition()->value('user_id'))
                ->orWhere(function (Builder $query) use ($supplierEmail): void {
                    $query->whereRaw('LOWER(email) = ?', [Str::lower((string) $supplierEmail)])
                        ->whereHas('department', function (Builder $query): void {
                            $query->where('slug', 'suppliers');
                        });
                })
                ->get();

            Notification::send($recipients, new UserActivityNotification(
                'payment_validated',
                'Pago validado',
                'El pago de la orden '.$lockedPurchaseOrder->folio.' fue validado. Ya puedes adjuntar el complemento de pago.',
            ));
        });

        return back()->with('success', 'El pago de la orden '.$purchaseOrder->folio.' quedó validado.');
    }
}
