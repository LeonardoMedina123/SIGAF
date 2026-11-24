<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Requisition;
use App\Models\Supplier;
use App\Models\SystemReimbursementDocument;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    public function index(): View
    {
        if (auth()->user()->isSupplier()) {
            $supplier = Supplier::query()
                ->whereRaw('LOWER(contact_email) = ?', [Str::lower(auth()->user()->email)])
                ->first();

            return view('suppliers.portal', [
                'purchaseOrders' => $supplier?->purchaseOrders()
                    ->with('supplier:id,rfc,name,contact_email')
                    ->with('requisition')
                    ->with('uploadedDocuments:id,purchase_order_id,kind')
                    ->latest('order_date')
                    ->get() ?? collect(),
            ]);
        }

        if (auth()->user()->isSystemsDepartment()) {
            return view('systems.portal', [
                'reimbursements' => $this->reimbursements(),
            ]);
        }

        $requisitions = Requisition::with([
            'uploadedDocuments:id,requisition_id,kind',
            'lastModifiedBy:id,name',
            'user.department',
            'purchaseOrder.supplier',
            'purchaseOrder.uploadedDocuments:id,purchase_order_id,kind,original_name,created_at',
        ])->latest()->get();

        return view('home', [
            'requisitions' => $requisitions,
            'requisitionDepartments' => Department::query()->orderBy('name')->pluck('name'),
            'suppliers' => Supplier::orderBy('name')->get(),
            'canValidatePayments' => auth()->user()->isAdministrator(),
            'canValidateFinancialResources' => auth()->user()->isFinancialsDepartment(),
        ]);
    }

    public function systemReimbursements(): View
    {
        return view('admin.system-reimbursements.index', [
            'reimbursements' => $this->reimbursements(),
        ]);
    }

    public function suppliers(): View
    {
        return view('suppliers.index', [
            'suppliers' => Supplier::orderBy('name')->paginate(20),
            'personTypes' => Supplier::PERSON_TYPES,
            'taxRegimes' => Supplier::TAX_REGIMES,
            'cfdiUses' => Supplier::CFDI_USES,
        ]);
    }

    /**
     * @return array<int, array{orderNumber: int, folio: string, document: ?SystemReimbursementDocument, editingEnabled: bool, status: string, modifiedAt: string}>
     */
    private function reimbursements(): array
    {
        $documents = SystemReimbursementDocument::query()
            ->get(['order_number', 'original_name', 'updated_at', 'editing_enabled'])
            ->keyBy('order_number');

        return array_map(
            static function (int $orderNumber) use ($documents): array {
                $document = $documents->get($orderNumber);

                return [
                    'orderNumber' => $orderNumber,
                    'folio' => 'Orden_TECNM_2515486',
                    'document' => $document,
                    'editingEnabled' => $document?->editing_enabled ?? false,
                    'status' => $document ? 'Enviado a administradores' : 'Pendiente de adjuntar',
                    'modifiedAt' => $document?->updated_at?->format('d/m/Y') ?? '—',
                ];
            },
            range(1, 9),
        );
    }
}
