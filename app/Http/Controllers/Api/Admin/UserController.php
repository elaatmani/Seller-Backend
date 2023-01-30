<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
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
     * Display the specified resource.
     * @param  Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request ,$id)
    {
        try {
            if(!$request->user()->can('users_show')){
                return response()->json([
                    'status' => false,
                    'code' => 'NOT_ALLOWED',
                    'message' => 'You Dont Have Access To See Users',
                    ],
                    405);
            }
            $user = User::find($id);
            if(isset($user)){
                return response()->json([
                    'status' => true,
                    'code' => 'USER_SUCCESS',
                    'data' => [
                        'user' => $user
                    ],
                    ],
                    200); 
            }
            return response()->json([
                'status' => false,
                'code' => 'USER_NOT_FOUND',
                'message' => 'User Does Not Exist'
                ],
                404);
        }catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
                'code' => 'SERVER_ERROR'],
                500);
        }
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
        try {
            if(!$request->user()->can('users_show')){
                return response()->json([
                    'status' => false,
                    'code' => 'NOT_ALLOWED',
                    'message' => 'You Dont Have Access To See Update Users',
                    ],
                    405);
            }
            $user = User::find($id);
            if(isset($user)){
                //validate
                $userValidator = Validator::make($request->all(),
                [
                    'firstname' => 'required',
                    'lastname' => 'required',
                    'phone' => 'required',
                    'email' => 'required|email|unique:users,email',
                    'password' => 'required',
                    'status' => 'required'
                ]);

                if($userValidator->fails()){
                    return response()->json([
                        'status' => false,
                        'code' => 'VALIDATION_ERROR',
                        'message' => 'validation error',
                        'error' => $userValidator->errors()],
                        401);
                }

                $user->firstname = $request->firstname;
                $user->lastname = $request->lastname;
                $user->phone = $request->phone;
                $user->email = $request->email;
                $user->password = Hash::make($request->password);
                $user->status = $request->status;
                    
                $user->save();

                return response()->json([
                    'status' => true,
                    'code' => 'USER_UPDATED',
                    'message' => 'User updated Successfully!'
                    ],
                    200); 
            }
            return response()->json([
                'status' => false,
                'code' => 'USER_NOT_FOUNDED',
                'message' => 'User Does Not Exist'
                ],
                404); 
        }catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
                'code' => 'SERVER_ERROR'],
                500);
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param Request $request 
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request ,$id)
    {
        try{
            if(!$request->user()->can('users_delete')){
                return response()->json([
                   'status' => false,
                   'code' => 'NOT_ALLOWED',
                   'message' => 'You Dont Have Access To Delete the User',
                   ],
                   405);
              }
            
               
            $user = User::find($id);
            if(isset($user)){
                User::where('id',$id)->delete();
                return response()->json([
                    'status' => true,
                    'code' => 'USER_DELETED',
                    'message' => 'User Deleted Successfully!',
                    200
                ]);
            }
            return  response()->json([
                        'status' => false,
                        'code' => 'USER_NOT_FOUND',
                        'message' => 'User Does Not Exist',
                        404]);

    }catch(\Throwable $th){
        return response()->json([
            'status' => false,
            'code' => 'SERVER_ERROR',
            'message' => $th->getMessage(),],
            500);
    }

    }
}
