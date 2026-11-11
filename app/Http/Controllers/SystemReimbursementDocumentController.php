<?php

namespace App\Http\Controllers;

use App\Models\SystemReimbursementDocument;
use App\Models\User;
use App\Notifications\UserActivityNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SystemReimbursementDocumentController extends Controller
{
    public function store(Request $request, int $orderNumber): RedirectResponse
    {
        $this->authorizeSystemsUser($request);

        $validated = $request->validate([
            'document' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ], [
            'document.required' => 'Selecciona un documento PDF.',
            'document.file' => 'El documento seleccionado no es válido.',
            'document.mimes' => 'El documento debe ser un archivo PDF.',
            'document.max' => 'El documento no puede superar 10 MB.',
        ]);

        $file = $validated['document'];

        DB::transaction(function () use ($file, $orderNumber): void {
            SystemReimbursementDocument::query()->updateOrCreate(
                ['order_number' => $orderNumber],
                [
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType() ?? 'application/pdf',
                    'content' => $file->getContent(),
                ],
            );

            $administrators = User::query()
                ->whereHas('department', function (Builder $query): void {
                    $query->where('slug', 'administrator');
                })
                ->get();

            Notification::send($administrators, new UserActivityNotification(
                'system_reimbursement_uploaded',
                'Documento de reembolso recibido',
                'El Departamento de Sistemas adjuntó el documento de la orden '.$orderNumber.'.',
            ));
        });

        return redirect()->route('home')->with('success', 'El documento se guardó correctamente.');
    }

    public function destroy(Request $request, int $orderNumber): RedirectResponse
    {
        $this->authorizeSystemsUser($request);

        $document = SystemReimbursementDocument::query()
            ->where('order_number', $orderNumber)
            ->firstOrFail();
        $document->delete();

        return redirect()->route('home')->with('success', 'El documento se eliminó correctamente.');
    }

    public function download(Request $request, int $orderNumber): StreamedResponse
    {
        $this->authorizeDocumentViewer($request);

        $document = SystemReimbursementDocument::query()
            ->where('order_number', $orderNumber)
            ->firstOrFail();
        $content = $document->content;

        return response()->streamDownload(
            static function () use ($content): void {
                echo $content;
            },
            'reembolso-'.$orderNumber.'.pdf',
            [
                'Content-Type' => $document->mime_type,
                'X-Content-Type-Options' => 'nosniff',
            ],
        );
    }

    private function authorizeSystemsUser(Request $request): void
    {
        $user = $request->user();
        abort_unless($user instanceof User && $user->isSystemsDepartment(), 403);
    }

    private function authorizeDocumentViewer(Request $request): void
    {
        $user = $request->user();
        abort_unless(
            $user instanceof User && ($user->isSystemsDepartment() || $user->isAdministrator()),
            403,
        );
    }
}
