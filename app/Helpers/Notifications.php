<?php
use App\Models\Notifications;
use App\Events\NewNotification;
use Illuminate\Support\Facades\Log;


if (!function_exists('toggle_notification')) {
    function toggle_notification($user_id,$message,$action,$opt,$options = []) {
        $notif = Notifications::create([
            'user_id' => $user_id,
            'message' => $message,
            'title'=> data_get($options,'title',null),
            'status'=> 'unread',
            'type'=> data_get($options,'type','normal'),
            'source'=> data_get($options,'source','system'),
            'priority'=> data_get($options,'priority','low'),
            'action'=> $action,
            'options'=> $opt,
        ]);
        // try {
            event(new NewNotification($user_id, $notif));
        // } catch (\Throwable $th) {
            
        // }
        return $notif;
    }
}