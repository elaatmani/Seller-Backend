<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Config;

class PusherController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $user = auth()->user();

        // Extract socket ID and channel name from the request
        $socketId = $request->input('socket_id');
        $channelName = $request->input('channel_name');

        // User data to be sent to the client
        $userData = [
            'user_id' => $user->id,
            'user_info' => [
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'role' => $user->roles()->first()?->name,
                'last_action' => $user->last_action,
                'id' => $user->id,
                // Add more user data as needed
            ],
        ];

        // JSON encode the user data
        $jsonUserData = json_encode($userData);

        // Prepare the string to sign
        $stringToSign = "$socketId:$channelName:$jsonUserData";

        // Pusher secret from the configuration
        $secret = Config::get('broadcasting.connections.pusher.secret');

        // Generate HMAC SHA256 hex digest
        $authSignature = hash_hmac('sha256', $stringToSign, $secret);

        // Prepare the auth string
        $auth = Config::get('broadcasting.connections.pusher.key') . ":$authSignature";

        // Prepare the response
        return response()->json([
            'auth' => $auth,
            'channel_data' => $jsonUserData,
        ]);
    }
}
