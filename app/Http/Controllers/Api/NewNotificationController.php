<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notifications;


class NewNotificationController extends Controller
{
    public function all(){
        $notifications = Notifications::where('user_id',auth()->id())->latest()->paginate(10);
        return response()->json([
            "data" => $notifications,
            "code"=>"SUCCESS"
        ]);
    }
    public function index(){
        $notifications = Notifications::latest()->where('user_id',auth()->id())->take(5)->get();
        $countnotif = Notifications::where('status', 'unread')->where('user_id',auth()->id())->count();
        $highlighted = Notifications::where('user_id', auth()->id())->where('status', 'unread')->latest()->first();

        return response()->json([
            "data" => $notifications,
            "count" => $countnotif,
            "highlighted"=>$highlighted,
            "code"=>"SUCCESS"
        ]);
    }

    public function MarkAsRead(){
        $change = Notifications::where('user_id', auth()->id())->update(['status' => 'read']);
        return response()->json([
            "code"=>"SUCCESS"
        ]);
    }
}
