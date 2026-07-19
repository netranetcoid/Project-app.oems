<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

/** UI inbox untuk notifikasi pengajuan yang berasal dari APK OvallHR. */
class OvallHrNotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notifications = $this->notifications($request)
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn (DatabaseNotification $notification): array => $this->payload($notification));

        return response()->json([
            'data' => [
                'unread_count' => $this->notifications($request)->whereNull('read_at')->count(),
                'notifications' => $notifications,
            ],
        ]);
    }

    public function open(Request $request, string $notification): RedirectResponse
    {
        $item = $this->notifications($request)->findOrFail($notification);
        $item->markAsRead();

        $destination = (string) data_get($item->data, 'url', route('hr.requests.index'));

        // Hanya redirect ke path internal yang sudah dibuat aplikasi sendiri.
        if (! str_starts_with($destination, url('/'))) {
            $destination = route('hr.requests.index');
        }

        return redirect()->to($destination);
    }

    private function notifications(Request $request)
    {
        return $request->user()
            ->notifications()
            ->where('type', \App\Notifications\OvallHrEmployeeRequestSubmitted::class);
    }

    private function payload(DatabaseNotification $notification): array
    {
        return [
            'id' => $notification->id,
            'title' => data_get($notification->data, 'title', 'Notifikasi OvallHR'),
            'message' => data_get($notification->data, 'message', ''),
            'read_at' => $notification->read_at?->toIso8601String(),
            'created_at' => $notification->created_at?->diffForHumans(),
            'open_url' => route('notifications.ovallhr.open', $notification->id),
        ];
    }
}
