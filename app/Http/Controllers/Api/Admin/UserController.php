<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\DeliveryPlace;
use App\Models\Product;
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
     * Display all users.
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            if (!$request->user()->can('show_all_users')) {
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
                            'last_action' => $user->last_action,
                            'status' => $user->status,
                            'created_at' => $user->created_at,
                            'updated_at' => $user->updated_at,
                            'role' => $user->roles->first(),
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
     * Display the specified user.
     * @param  Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {

        try {
            if (!$request->user()->can('view_user')) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_ALLOWED',
                        'message' => 'You Dont Have Access To See Users',
                    ],
                    405
                );
            }
            $user = User::with(['products', 'deliveryPlaces.city', 'city'])->find($id);

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
                                'product' => $user->roles->pluck('id')->first() === 2 ? $user->products->pluck('id') : null,
                                'deliveryPlaces' => $user->deliveryPlaces,
                                'city' => $user->city ? $user->city : null,
                                'having_all' => $user->having_all,
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
     * Create the user
     * @param Request $request
     * @return User
     */
    public function create(Request $request)
    {
        try {

            if (!$request->user()->can('create_user')) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_ALLOWED',
                        'message' => 'You Dont Have Access To Create User',
                    ],
                    405
                );
            }
            //Validated
            $validateUser = Validator::make(
                $request->all(),
                [
                    'firstname' => 'required',
                    'lastname' => 'required',
                    'phone' => 'required',
                    'email' => 'required|email|unique:users,email',
                    'password' => 'required',
                    'status' => 'required',
                    'role' => 'required',
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


            $user = User::create([
                'firstname' => $request->firstname,
                'lastname' => $request->lastname,
                'phone' => $request->phone,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'status' => $request->status,
                'having_all' => $request->having_all
            ]);


            if ($request->role === 2 && isset($request->product_id)) {
                $productIds = is_array($request->product_id) ? $request->product_id : [$request->product_id];

                if ($user->having_all) {
                    $products = Product::all();
                    $productIds = $products->pluck('id')->toArray();
                }

                // Add new product assignments
                foreach ($productIds as $productId) {
                    ProductAgente::updateOrCreate([
                        'agente_id' => $user->id,
                        'product_id' => $productId
                    ]);
                }
            }


            if ($request->role === 3) {
                $user->city = $request->city;
                $user->save();
                foreach ($request->deliverycity as $city) {
                    DeliveryPlace::create([
                        'delivery_id' => $user->id,
                        'city_id' => $city['city_id'],
                        'fee' => $city['fee']
                    ]);
                }
            }


            $role = Role::where('id', $request->role)->value('name');
            $user->assignRole($role);


            return response()->json(
                [
                    'status' => true,
                    'code' => 'USER_CREATED',
                    'message' => 'User Created Successfully!',
                    'token' => $user->createToken("API TOKEN")->plainTextToken,
                    'user' => $user
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
     * Update the specified user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            if (!$request->user()->can('update_user')) {
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

                if ($user->roles->first()) {

                    if ($user->roles->first()->name != $request->role) {

                        // remove existing role
                        $user->removeRole($user->roles->first()->name);
                        $role = Role::where('id', $request->role)->value('name');
                        // assign new role
                        $user->assignRole($role);
                    }
                } else {
                    $role = Role::where('id', $request->role)->value('name');

                    $user->assignRole($role);
                }
                if ($request->role === 3) {
                    $user->city = $request->city;

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
                $user->save();



                if ($request->role == 2) {
                    $having_all = $request->having_all == 'true' ? 1 : 0;

                    if($having_all && $having_all != $user->having_all) {

                        $user->having_all = $having_all;
                        $user->save();
                            $products = Product::all();
                            $productIds = $products->pluck('id')->toArray();
                            foreach ($productIds as $productId) {
                                ProductAgente::updateOrCreate([
                                    'agente_id' => $user->id,
                                    'product_id' => $productId
                                ]);
                            }

                        } else {

                            $productIds = is_array($request->product_id) ? $request->product_id : [$request->product_id];
                            // Get current product IDs assigned to the user
                            $currentProductIds = ProductAgente::where('agente_id', $user->id)->pluck('product_id')->toArray();

                            // Delete product assignments that are not in the new list
                            $deleteProductIds = array_diff($currentProductIds, $productIds);

                            ProductAgente::where('agente_id', $user->id)->whereIn('product_id', $deleteProductIds)->delete();

                            // Add new product assignments
                            foreach ($productIds as $productId) {
                                ProductAgente::updateOrCreate([
                                    'agente_id' => $user->id,
                                    'product_id' => $productId
                                ]);
                            }
                        }

                }



                return response()->json(
                    [
                        'status' => true,
                        'code' => 'USER_UPDATED',
                        'message' => 'User updated Successfully!',
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
     * Remove the specified user.
     * @param Request $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request, $id)
    {
        try {
            if (!$request->user()->can('delete_user')) {
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

                $role =  $user->getRoleNames()->first();
                if ($role) {
                    $user->removeRole($role);
                }
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
     * Update the specified user status.{ active | desactive }
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateUserStatus(Request $request, $id)
    {

        try {
            if (!$request->user()->can('update_user_status')) {
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
     * Display a listing of roles.
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function roles(Request $request)
    {
        try {
            if (!$request->user()->can('show_all_roles')) {
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
                $query->select('name','description');
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
     * Display the specified role.
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function showRole(Request $request, $id)
    {
        try {
            if (!$request->user()->can('view_role')) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_ALLOWED',
                        'message' => 'You Dont Have Access To See All Roles',
                    ],
                    405
                );
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
     * Create a role.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function createRole(Request $request)
    {
        try {
            if (!$request->user()->can('create_role')) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_ALLOWED',
                        'message' => 'You Dont Have Access To See All Roles',
                    ],
                    405
                );
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
     * Update a specified role.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateRole(Request $request, $id)
    {
        try {
            if (!$request->user()->can('update_role')) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_ALLOWED',
                        'message' => 'You Dont Have Access To See All Roles',
                    ],
                    405
                );
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
            if ($role->id === 1 || $role->id == 2 || $role->id == 3) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_ALLOWED',
                        'message' => 'You Dont Have Rights to Update this Role',
                    ],
                    405
                );
            }
            $role->update(['name' => $request->name]);
            $role->syncPermissions([$request->permissions]);


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
     * Remove the specified role.
     * @param \Illuminate\Http\Request $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deleteRole(Request $request, $id)
    {
        try {
            if (!$request->user()->can('delete_role')) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_ALLOWED',
                        'message' => 'You Dont Have Access To See All Roles',
                    ],
                    405
                );
            }



            $role = Role::findById($id);
            if ($role->id === 1 || $role->id  == 2 || $role->id  == 3) {
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
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function allCities(Request $request)
    {

        $cities = City::orderBy('name')->get();

        return response()->json(
            [
                'status' => true,
                'code' => 'SUCCESS',
                'data' => $cities
            ],
            200
        );
    }


    /**
     * Show all cities.
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function delevries(Request $request)
    {
        $deliveryRole = Role::where('name', 'delivery')->first();
        $deliveries = $deliveryRole->users()->get();

        return response()->json(
            [
                'status' => true,
                'code' => 'SUCCESS',
                'data' => $deliveries
            ],
            200
        );
    }


    /**
     * Show all agents.
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function agents(Request $request)
    {
        $agenteRole = Role::where('name', 'agente')->first();
        $agents = $agenteRole->users()->get();

        return response()->json(
            [
                'status' => true,
                'code' => 'SUCCESS',
                'data' => $agents
            ],
            200
        );
    }

    /**
     * Display the specified user.
     * @param  Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function profile(Request $request)
    {

        try {


            return response()->json(
                [
                    'status' => true,
                    'code' => 'USER_SUCCESS',
                    'data' => [
                        'user' =>  $request->user()
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
     * Update the specified user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateProfile(Request $request)
    {
        try {

            $user = User::find($request->user()->id);

            if (isset($user)) {

                //validate
                $userValidator = Validator::make(
                    $request->all(),
                    [
                        'firstname' => 'required',
                        'lastname' => 'required',
                        'phone' => 'required',
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
                if ($request->updatePassword == 'true') {
                    $user->password = Hash::make($request->password);
                }

                $user->save();

                return response()->json(
                    [
                        'status' => true,
                        'code' => 'USER_UPDATED',
                        'message' => 'Profile updated Successfully!',
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
     * Show Online Users.
     * @return \Illuminate\Http\Response
     */
    public function onlineUsers()
    {
        $usersOnline = User::whereBetween('last_action', [now()->subMinutes(1), now()])->get();

        return response()->json(
            [
                'status' => true,
                'code' => 'SUCCESS',
                'data' => $usersOnline
            ],
            200
        );
    }
}
