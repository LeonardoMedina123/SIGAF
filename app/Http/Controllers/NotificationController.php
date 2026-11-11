<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        return view('notifications.index', [
            'notifications' => $request->user()->notifications()->latest()->paginate(20),
        ]);
    }

    public function markAsRead(Request $request, string $notification): JsonResponse|RedirectResponse
    {
        $databaseNotification = $request->user()->notifications()->findOrFail($notification);

        if ($databaseNotification->read_at === null) {
            $databaseNotification->markAsRead();
        }

        if ($request->expectsJson()) {
            return response()->json([
                'unread_count' => $request->user()->unreadNotifications()->count(),
            ]);
        }

        return back()->with('success', 'La notificación se marcó como leída.');
    }
}
