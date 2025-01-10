<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function markAsRead($notificationId)
    {
        $notification = Notification::find($notificationId);
        if ($notification && $notification->receiver_id === auth()->id()) {
            $notification->is_read = true;
            $notification->read_at = now();
            $notification->save();

            return response()->json(['success' => true]);
        }
        return response()->json(['error' => 'Unauthorized'], 403);
    }
}
