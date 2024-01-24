<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Option;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index(Request $request) {
        return response()->json([
            'keys' => $request->keys
        ]);
    }
}
