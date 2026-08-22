<?php

namespace App\Http\Controllers\Operations;

use App\Domains\Operations\Actions\MarkAllNotificationsRead;
use App\Domains\Operations\Actions\MarkNotificationRead;
use App\Domains\Operations\Models\OpsNotification;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', OpsNotification::class);

        $notifications = OpsNotification::query()
            ->where('user_id', $request->user()->id)
            ->when($request->string('unread')->toString() === '1', fn ($q) => $q->whereNull('read_at'))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('Operations/Notifications/Index', [
            'notifications' => $notifications,
            'filters' => ['unread' => $request->string('unread')->toString()],
            'unreadCount' => OpsNotification::query()
                ->where('user_id', $request->user()->id)
                ->whereNull('read_at')
                ->count(),
            'can' => [
                'update' => $request->user()?->can('notifications.update') ?? false,
            ],
        ]);
    }

    public function markRead(OpsNotification $notification, MarkNotificationRead $action): RedirectResponse
    {
        $this->authorize('markRead', $notification);
        $action->handle($notification);

        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllRead(Request $request, MarkAllNotificationsRead $action): RedirectResponse
    {
        $this->authorize('viewAny', OpsNotification::class);
        $action->handle($request->user());

        return back()->with('success', 'All notifications marked as read.');
    }
}
