<?php

namespace App\Events;

use App\Models\Notification;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NotificationSent implements ShouldBroadcastNow
{
    use InteractsWithSockets, SerializesModels;

    public $notification;

    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
        
    }

    public function broadcastOn()
    {
        return new PrivateChannel('user.' . $this->notification->user_id);
    }


    public function broadcastAs()
    {
        return 'notification.sent';
    }

    public function broadcastWith()
    {
        // echo "NotificationSent for {$this->notification->user_id}: {$this->notification->message}\n";
        $notificatios = Notification::where('user_id', $this->notification->user_id)
                ->where('message_status', false)->count();
        Log::info("User {$this->notification->user_id} Recieved: {$this->notification->message}");
        Log::info($notificatios);
        return [
            'id' => $this->notification->id,
            'message' => $this->notification->message,
            'user_id' => $this->notification->user_id,
            'count' => $notificatios,
        ];
        
    }

}
