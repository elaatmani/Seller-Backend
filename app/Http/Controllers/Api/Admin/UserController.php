<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;


class UserController extends Controller


{
    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            if(!$request->user()->can('users_show')){
                return response()->json([
                    'status' => false,
                    'code' => 'NOT_ALLOWED',
                    'message' => 'You Dont Have Access To See All Users',
                    ],
                    405);
            }
            $users = User::all();
            return response()->json([
                'status' => true,
                'code' => 'SHOW_ALL_USERS',
                'data' => [
                    'users' => $users
                ],
                ],
                200);
        }catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
                'code' => 'SERVER_ERROR'],
                500);
        }
    }

    /**
     * Display a listing of the Roles.
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function roles(Request $request)
    {
        try {
            if(!$request->user()->can('users_show')){
                return response()->json([
                    'status' => false,
                    'code' => 'NOT_ALLOWED',
                    'message' => 'You Dont Have Access To See All Roles',
                    ],
                    405);
            }
            $roles = Role::get(['id', 'name']);
            return response()->json([
                'status' => true,
                'code' => 'SUCCESS',
                'data' => [
                    'roles' => $roles
                ],
                ],
                200); 
        }catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
                'code' => 'SERVER_ERROR'],
                500);
        }
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
        //
    }
}
