<?php

namespace App\Services;

use App\Events\NotificationSent;
use App\Models\Flight;
use App\Models\Notification;
use App\Models\User;
use App\Models\User_flight;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class NotificationService
{

    public function send_notification(int $user_ID, string $message)
    {
        $notification = Notification::query()->create([
          'user_id' => $user_ID,
          'message' => $message,
        ]);  

        event(new NotificationSent($notification));
    }

    public function get_all_notifications()
    {
        $user = Auth::user();
        if ($user['role_id'] != 3) {
            return ['message' => 'Authorization required'];
        }
        $notificatios = Notification::where('user_id', $user->id)->latest()->get();

        Notification::where('user_id', $user->id)->update([ 'message_status' => true ]);
        
        return ['notificatios' => $notificatios]; 
    }

    public function new_notifications_count()
    {
        $user = Auth::user();
        $notificatios = Notification::where('user_id', $user->id)->where('message_status', false)->count();
        return $notificatios;
    }

    public function send_flight_reminders()
    {
        $today = Carbon::now('Asia/Damascus');
        $tomorrow = $today->copy()->addDay()->format('Y-m-d');

        $flights = Flight::whereDate('date', $tomorrow)->get();

        foreach ($flights as $flight) {
            
            $userFlights = User_flight::where('flight_id', $flight->id)->get();
            foreach ($userFlights as $userFlight) {
                $message = "Reminder: Your flight ({$flight->flight_number}) is scheduled for tomorrow at {$flight->start_time}. Please be ready for your trip.";

                $this->send_notification($userFlight->user_id, $message);
            }
        }
    }

}