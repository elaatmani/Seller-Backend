<?php
use App\Models\Notifications;

if (!function_exists('toggle_notification')) {
    function toggle_notification($user_id,$message,$action,$options = []) {
        $notif = Notifications::create([
            'user_id' => $user_id,
            'message' => $message,
            'title'=> data_get($options,'title',null),
            'status'=> 'unread',
            'type'=> data_get($options,'type','normal'),
            'source'=> data_get($options,'source','system'),
            'priority'=> data_get($options,'priority','low'),
            'action'=> $action,
        ]);
        return $notif;
    }
}