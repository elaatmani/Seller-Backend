<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\DeliveryPlace;
use App\Models\ProductAgente;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;

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
            if (!$request->user()->can('users_show')) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_ALLOWED',
                        'message' => 'You Dont Have Access To See All Users',
                    ],
                    405
                );
            }
            $users = User::with('role')->get();;
            return response()->json([
                'status' => true,
                'code' => 'SHOW_ALL_USERS',
                'data' => [
                    'users' => $users->map(function ($user) {
                        return [
                            'id' => $user->id,
                            'firstname' => $user->firstname,
                            'lastname' => $user->lastname,
                            'phone' => $user->phone,
                            'email' => $user->email,
                            'photo' => $user->photo,
                            'is_online' => $user->is_online,
                            'status' => $user->status,
                            'created_at' => $user->created_at,
                            'updated_at' => $user->updated_at,
                            'role_name' => $user->roles->pluck('name')->first(),
                        ];
                    })
                ],
            ], 200);
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
     * Display the specified resource.
     * @param  Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        try {
            if (!$request->user()->can('users_show')) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_ALLOWED',
                        'message' => 'You Dont Have Access To See Users',
                    ],
                    405
                );
            }
            $user = User::with('products')->find($id);
            if (isset($user)) {
                return response()->json(
                    [
                        'status' => true,
                        'code' => 'USER_SUCCESS',
                        'data' => [
                            'user' =>  [
                                'id' => $user->id,
                                'firstname' => $user->firstname,
                                'lastname' => $user->lastname,
                                'phone' => $user->phone,
                                'email' => $user->email,
                                'is_online' => $user->is_online,
                                'status' => $user->status,
                                'created_at' => $user->created_at,
                                'updated_at' => $user->updated_at,
                                'role' => $user->roles->pluck('id')->first(),
                                'product' => $user->roles->pluck('id')->first() === 2 ? $user->products->value('id') : null,
                            ]
                        ],
                    ],
                    200
                );
            }
            return response()->json(
                [
                    'status' => false,
                    'code' => 'NOT_FOUND',
                    'message' => 'User Does Not Exist'
                ],
                404
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            if (!$request->user()->can('users_show')) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_ALLOWED',
                        'message' => 'You Dont Have Access To See Update Users',
                    ],
                    405
                );
            }
            $user = User::find($id);

            if (isset($user)) {
                //validate
                $userValidator = Validator::make(
                    $request->all(),
                    [
                        'firstname' => 'required',
                        'lastname' => 'required',
                        'phone' => 'required',
                        'email' =>  [
                            'required',
                            'email',
                            Rule::unique('users')->ignore($user),
                        ],
                        'status' => 'required',
                        'role' => 'required'
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



                $user->firstname = $request->firstname;
                $user->lastname = $request->lastname;
                $user->phone = $request->phone;
                $user->email = $request->email;
                if ($request->updatePassword == 'true') {
                    $user->password = Hash::make($request->password);
                }
                $user->status = $request->status;


                if ($user->roles->first()->name != $request->role) {

                    // remove existing role
                    $user->removeRole($user->roles->first()->name);
                    $role = Role::where('id', $request->role)->value('name');
                    // assign new role
                    $user->assignRole($role);
                }

                $user->save();

                if ($request->role === 2) {
                    $productAgente = ProductAgente::where('agente_id', $user->id)->first();
                    if ($productAgente) {
                        $productAgente->product_id = $request->product_id;
                        $productAgente->save();
                    }
                }

                if ($request->role === 3) {
                    $existingCityIds = DeliveryPlace::where('delivery_id', $id)->pluck('city_id');

                    foreach ($request->input('deliverycity') as $city) {
                        $deliveryPlace = DeliveryPlace::where('delivery_id', $id)
                            ->where('city_id', $city['city_id'])
                            ->first();

                        if ($deliveryPlace) {
                            // If the delivery place already exists, update its fee and city_id values
                            $deliveryPlace->update(['fee' => $city['fee'], 'city_id' => $city['city_id']]);
                        } else {
                            // If the delivery place does not exist, create a new one
                            DeliveryPlace::create([
                                'delivery_id' => $id,
                                'city_id' => $city['city_id'],
                                'fee' => $city['fee']
                            ]);
                        }
                    }

                    // Delete any delivery places that are not in the $request object
                    $cityIdsToDelete = $existingCityIds->diff(collect($request->input('deliverycity'))->pluck('city_id')->toArray());

                    if (!empty($cityIdsToDelete)) {
                        DeliveryPlace::where('delivery_id', $id)
                            ->whereIn('city_id', $cityIdsToDelete)
                            ->delete();
                    }
                }

                return response()->json(
                    [
                        'status' => true,
                        'code' => 'USER_UPDATED',
                        'message' => 'User updated Successfully!',
                        'data' => $user->password
                    ],
                    200
                );
            }
            return response()->json(
                [
                    'status' => false,
                    'code' => 'NOT_FOUND',
                    'message' => 'User Does Not Exist'
                ],
                404
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
     * Remove the specified resource from storage.
     * @param Request $request 
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request, $id)
    {
        try {
            if (!$request->user()->can('users_delete')) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_ALLOWED',
                        'message' => 'You Dont Have Access To Delete the User',
                    ],
                    405
                );
            }


            $user = User::find($id);
            if (isset($user)) {
                User::where('id', $id)->delete();
                return response()->json([
                    'status' => true,
                    'code' => 'USER_DELETED',
                    'message' => 'User Deleted Successfully!',
                    200
                ]);
            }
            return  response()->json([
                'status' => false,
                'code' => 'NOT_FOUND',
                'message' => 'User Does Not Exist',
                404
            ]);
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


    /**
     * Update account
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateAccount(Request $request)
    {
        try {
            if (!$request->user()->can('user_update')) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_ALLOWED',
                        'message' => 'You Dont Have Access To See Update Users',
                    ],
                    405
                );
            }

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
     * Show account Infos
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */


    /**
     * Status User Account
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
     * Update User Status
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateUserStatus(Request $request, $id)
    {

        try {
            if (!$request->user()->can('users_update')) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_ALLOWED',
                        'message' => 'You Dont Have Access To See Update Users',
                    ],
                    405
                );
            }
            $user = User::find($id);

            //test if status false or true
            if ($request->status == 'true') {
                $userStatus = 1;
            } else {
                $userStatus = 0;
            }

            $user->status = $userStatus;
            $user->save();

            return response()->json(
                [
                    'status' => true,
                    'code' => 'STATUS_USER_UPDATED',
                    'message' => 'User Status Updated Successfully!',
                    'data' => $user->status
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
     * Display a listing of the Roles.
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function roles(Request $request)
    {
        try {
            if (!$request->user()->can('users_show')) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_ALLOWED',
                        'message' => 'You Dont Have Access To See All Roles',
                    ],
                    405
                );
            }
            $roles = Role::with(['permissions' => function ($query) {
                $query->select('name');
            }])->get(['id', 'name']);
            return response()->json(
                [
                    'status' => true,
                    'code' => 'SUCCESS',
                    'data' => [
                        'roles' => $roles
                    ],
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
     * Display the specified resource.
     * @param  Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function showRole(Request $request, $id)
    {
        try {
            if (!$request->user()->hasRole('admin')) {
                return response()->json([
                    'status' => false,
                    'code' => 'NOT_ALLOWED',
                    'message' => 'You Dont Have Access To Create Role',
                ], 405);
            }

            $role = Role::findOrFail($id);
            $groupsWithRoles = $role->getPermissionNames();


            return response()->json(
                [
                    'status' => false,
                    'code' => 'ROLE_PERMISSION_SUCCESS',
                    'message' => 'ROLE and PERMISSION Fetched SUCCESFULLY!',
                    'data' => [
                        'role' => $role->name,
                        'permission' => $groupsWithRoles,
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
     * Create Roles
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function createRole(Request $request)
    {
        try {
            if (!$request->user()->hasRole('admin')) {
                return response()->json([
                    'status' => false,
                    'code' => 'NOT_ALLOWED',
                    'message' => 'You Dont Have Access To Create Role',
                ], 405);
            }

            //Validated
            $validateUser = Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'permissions' => 'required'
                ]
            );

            if ($validateUser->fails()) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'VALIDATION_ERROR',
                        'message' => 'validation error',
                        'error' => $validateUser->errors()
                    ],
                    401
                );
            }


            $role = Role::create(['name' => $request->name]);
            $role->givePermissionTo($request->permissions);

            return response()->json(
                [
                    'status' => true,
                    'code' => 'ROLE_ADDED',
                    'message' => 'Role and Permissions Added Successfully!'
                ],
                200
            );
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'status' => false,
                    'code' => 'SERVER_ERROR',
                    'message' => $th->getMessage(),
                    'error' => $validateUser->errors()
                ],
                500
            );
        }
    }


    /**
     * Update Roles
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateRole(Request $request, $id)
    {
        try {
            if (!$request->user()->hasRole('admin')) {
                return response()->json([
                    'status' => false,
                    'code' => 'NOT_ALLOWED',
                    'message' => 'You Dont Have Access To Update Roles',
                ], 405);
            }

            //Validated
            $validateUser = Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'permissions' => 'required'
                ]
            );



            if ($validateUser->fails()) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'VALIDATION_ERROR',
                        'message' => 'validation error',
                        'error' => $validateUser->errors()
                    ],
                    401
                );
            }
            $role = Role::findById($id);
            if ($role === 1 || $role == 2 || $role == 3) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_ALLOWED',
                        'message' => 'You Dont Rights to Update this Role',
                    ],
                    405
                );
            }
            $role->update(['name' => $request->name]);
            $role->syncPermissions($request->permissions);


            return response()->json(
                [
                    'status' => true,
                    'code' => 'ROLE_UPDATED',
                    'message' => 'Role and Permissions Updated Successfully!'
                ],
                200
            );
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'status' => false,
                    'code' => 'SERVER_ERROR',
                    'message' => $th->getMessage(),
                    'error' => $validateUser->errors()
                ],
                500
            );
        }
    }


    /**
     * Remove the specified resource from storage.
     * @param Request $request 
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deleteRole(Request $request, $id)
    {
        try {
            if (!$request->user()->hasRole('admin')) {
                return response()->json([
                    'status' => false,
                    'code' => 'NOT_ALLOWED',
                    'message' => 'You Dont Have Access To See Delete Roles',
                ], 405);
            }



            $role = Role::findById($id);
            if ($role === 1 || $role == 2 || $role == 3) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_ALLOWED',
                        'message' => 'You Dont Rights to Update this Role',
                    ],
                    405
                );
            }
            $role->revokePermissionTo(Permission::all());
            $role->delete();


            return response()->json(
                [
                    'status' => true,
                    'code' => 'ROLE_DELETED',
                    'message' => 'Role and Permissions Deleted Successfully!'
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


    /**
     * Show All Cities.
     * @param Request $request 
     * @return \Illuminate\Http\Response
     */
    public function allCities(Request $request)
    {
        if (!$request->user()->hasRole('admin')) {
            return response()->json([
                'status' => false,
                'code' => 'NOT_ALLOWED',
                'message' => 'You Dont Have Access To See Delete Roles',
            ], 405);
        }

        $cities  = City::all();

        return response()->json(
            [
                'status' => true,
                'code' => 'SUCCESS',
                'data' => $cities
            ],
            200
        );
    }



    public function delevries(Request $request)
    {
        $deliveryRole = Role::where('name', 'delivery')->first();
        $deliveries = $deliveryRole->users()->get();
        return $deliveries;
    }
}
