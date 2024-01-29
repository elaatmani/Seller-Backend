<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Option;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index(Request $request)
    {
        return response()->json([
            'keys' => $request->keys
        ]);
    }

    public function create(Request $request)
    {
        try {
            if (!$request->user()->hasRole('admin')) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_ALLOWED',
                        'message' => 'You Dont Have Access To Create Settings.',
                    ],
                    405
                );
            }

            foreach ($request->all() as $value) {
                Option::create(
                    [
                        'name' => $value['name'],
                        'value' => $value['value']
                    ],
                );
            }

            return response()->json(
                [
                    'status' => true,
                    'code' => 'SUCCESS',
                ],
                200
            );
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'status' => false,
                    'code' => 'SERVER_ERROR',
                    'message' => $th->getMessage(),
                ],
                500
            );
        }
    }
}
