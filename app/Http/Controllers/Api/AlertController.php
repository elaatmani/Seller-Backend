<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);

        $alerts = Alert::paginate($perPage);

        return response()->json([
            'code' => 'SUCCESS',
            'alerts' => $alerts
        ]);
    }

    public function alerts(Request $request) {
        $role = auth()->user()->roles()->first()->name;
        $id = auth()->id();

        $alerts = Alert::where([
            'type' => 'global'
        ])
        ->orWhere([
            ['type', 'role'],
            ['target', $role],
        ])
        ->orWhere([
            ['type', 'user'],
            ['target', $id],
        ])->get();

        return response()->json([
            'code' => 'SUCCESS',
            'alerts' => $alerts
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {

            if(!auth()->user()->hasRole('admin')) {
                return response()->json([
                    'message' => 'Not allowed'
                ], 403);
            }


            $alert = Alert::create([
                'variant' => $request->input('variant'),
                'type' => $request->input('type'),
                'target' => $request->input('target'),
                'content' => $request->input('content'),
                'closeable' => $request->input('closeable'),
            ]);

            return response()->json([
                'code' => 'SUCCESS',
                'alert' => $alert
            ]);

        } catch (\Throwable $th) {
            return response()->json(['code' => 'SERVER_ERROR', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Alert::where('id', $id)->delete();

        return response()->json([
            'code' => 'SUCCESS'
        ], 200);
    }
}
