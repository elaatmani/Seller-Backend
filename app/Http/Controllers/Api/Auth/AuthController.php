<?php

namespace App\Http\Controllers\Api\Auth;

use App\Models\User;
use App\Http\Controllers\Controller;
use App\Models\DeliveryPlace;
use App\Models\ProductAgente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AuthController extends Controller
{


   /**
     * Update authentificated account.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateAccount(Request $request)
    {
        try {
            //validate
            $userValidator = Validator::make(
                $request->all(),
                [
                    'firstname' => 'required',
                    'lastname' => 'required',
                    'phone' => 'required',
                    'password' => 'required',
                    'status' => 'required'
                ]
            );

            if ($userValidator->fails()) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'VALIDATION_ERROR',
                        'message' => 'validation error',
                        'error' => $userValidator->errors()
                    ],
                    401
                );
            }


            $user = User::find($request->user()->id);




            $user->firstname = $request->firstname;
            $user->lastname = $request->lastname;
            $user->phone = $request->phone;
            $user->password = Hash::make($request->password);
            $user->status = $request->status;

            $user->save();

            return response()->json(
                [
                    'status' => true,
                    'code' => 'USER_UPDATED',
                    'message' => 'User updated Successfully!'
                ],
                200
            );
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'status' => false,
                    'message' => $th->getMessage(),
                    'code' => 'SERVER_ERROR'
                ],
                500
            );
        }
    }



    /**
     * Display athentificated account.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function showUserAccount(Request $request)
    {
        try {
            $user = $request->user();
            return response()->json(
                [
                    'status' => true,
                    'code' => 'USER_SHOWED',
                    'data' => [
                        'user' => $user,
                    ]
                ],
                200
            );
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'status' => false,
                    'message' => $th->getMessage(),
                    'code' => 'SERVER_ERROR'
                ],
                500
            );
        }
    }



    /**
     * Login user
     * @param Request $request
     * @return User
     */
    public function loginUser(Request $request)
    {
        try {
            //Validated
            $validateUser = Validator::make(
                $request->all(),
                [
                    'email' => 'required|email',
                    'password' => 'required'
                ]
            );

            if ($validateUser->fails()) {
                return response()->json(
                    [
                        'status' => false,
                        'message' => 'validation error',
                        'code' => 'VALIDATION_ERROR',
                        'error' => $validateUser->errors()
                    ],
                    401
                );
            }

            if (!Auth::attempt($request->only(['email', 'password']))) {
                return response()->json([
                    'status' => false,
                    'code' => 'INVALID_CREDENTIALS',
                    'message' => 'Email & Password does not match with our record.',
                ], 401);
            }

            $user = User::where([['email', $request->email], ['status', 1]])->first();
            if (isset($user)) {


                $role = Role::findOrFail($user->roles->first()->id);
                $groupsWithRoles = $role->getPermissionNames();


                return response()->json(
                    [
                        'status' => true,
                        'message' => 'User Logged In Successfully!',
                        'code' => 'AUTHENTICATION_SUCCESSFUL',
                        'data' => [
                            'token' => $user->createToken("API TOKEN")->plainTextToken,
                            'user' => [
                                'id' => $user->id,
                                'firstname' => $user->firstname,
                                'lastname' => $user->lastname,
                                'email' => $user->email,
                                'photo' => $user->photo,
                                'phone' => $user->phone,
                                'status' => $user->status,
                                "is_online" => $user->is_online,
                                'role' => $user->roles->first()->name,
                                'permissions' => $groupsWithRoles
                            ]


                        ]
                    ],
                    200
                );
            } else {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_ACTIVE_ERROR',
                        'message' => 'You dont Have Access anymore!',
                    ],
                    405
                );
            }
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'status' => false,
                    'message' => $th->getMessage(),
                    'code' => 'SERVER_ERROR',
                    'error' => $validateUser->errors()
                ],
                500
            );
        }
    }




    /**
     * Logout user
     * @param Request $request
     * @return User
     */
    public function logoutUser(Request $request)
    {
        try {
            // $request->user()->tokens()->delete();
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return response()->json(
                [
                    'status' => true,
                    'code' => 'LOGOUT_SUCCESSFUL',
                    'message' => 'User Logged Out Successfully!',
                ],
                200
            );
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'status' => false,
                    'code' => 'SERVER_ERROR',
                    'message' => $th->getMessage()
                ],
                500
            );
        }
    }




    /**
     * Display user permission
     * @param Request $request
     * @return User
     */
    public function userPermission(Request $request)
    {
        $role = Role::findOrFail($request->user()->roles->first()->id);
        $groupsWithRoles = $role->getPermissionNames();
        return response()->json(
            [
                'status' => true,
                'code' => 'SUCCESS',
                'data' => [
                    'permissions' => $groupsWithRoles,
                ],
            ],
            200
        );
    }
}
