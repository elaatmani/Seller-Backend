<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Intervention\Image\Facades\Image;
use Illuminate\Validation\Rule;


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
                        'user' =>  [
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
                                'role' => $user->roles->pluck('id')->first(),      
                            ]
                    ],
                    ],
                    200); 
            }
            return response()->json([
                'status' => false,
                'code' => 'NOT_FOUND',
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
                    'email' =>  [
                        'required',
                        'email',
                        Rule::unique('users')->ignore($user),
                    ],
                    'status' => 'required',
                    'role' => 'required'
                ]);

                if($userValidator->fails()){
                    return response()->json([
                        'status' => false,
                        'code' => 'VALIDATION_ERROR',
                        'message' => 'validation error',
                        'error' => $userValidator->errors()],
                        401);
                }

                 //Upload User Photo
                if($request->hasFile('user_image')){
                    $image_tmp = $request->file('user_image');
                    if($image_tmp->isValid()){
                        // Get Image Extension
                        $extension = $image_tmp->getClientOriginalExtension();
                        // Generate New Image Name
                        $imageName = rand(111,99999).'.'.$extension;
                        $imagePath = 'account/user/images/'.$imageName;
                        // Upload the Image
                        Image::make($image_tmp)->save($imagePath);

                        $oldImage = $user->photo;
                        if(!empty($oldImage)){
                            if(file_exists('account/user/images/'.$oldImage)){
                                unlink('account/user/images/'.$oldImage);
                            }
                        }
                    }
                }else if(!empty($user->image)){
                    $imageName = $user->image;
                }else{
                    $imageName = "";
                }

                $user->firstname = $request->firstname;
                $user->lastname = $request->lastname;
                $user->phone = $request->phone;
                $user->email = $request->email;
                $user->photo = $imageName;
                if($request->updatePassword == 'true'){
                    $user->password = Hash::make($request->password);
                }else{
                    $user->password = $user->password;
                }
                $user->status = $request->status;

                // remove existing role
                $user->removeRole($user->roles->first()->name);
                $role = Role::where('id', $request->role)->value('name');
                // assign new role
                $user->assignRole($role);
                $user->save();

                return response()->json([
                    'status' => true,
                    'code' => 'USER_UPDATED',
                    'message' => 'User updated Successfully!',
                    'data' => $user->password
                    ],
                    200); 
            }
            return response()->json([
                'status' => false,
                'code' => 'NOT_FOUND',
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
                        'code' => 'NOT_FOUND',
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


    /**
     * Update account
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateAccount(Request $request)
    {
        try {
            if(!$request->user()->can('user_update')){
                return response()->json([
                    'status' => false,
                    'code' => 'NOT_ALLOWED',
                    'message' => 'You Dont Have Access To See Update Users',
                    ],
                    405);
            }

                //validate
                $userValidator = Validator::make($request->all(),
                [
                    'firstname' => 'required',
                    'lastname' => 'required',
                    'phone' => 'required',
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
                

                $user = User::find($request->user()->id);

                //Upload User Photo
                if($request->hasFile('user_image')){
                    $image_tmp = $request->file('user_image');
                    if($image_tmp->isValid()){
                        // Get Image Extension
                        $extension = $image_tmp->getClientOriginalExtension();
                        // Generate New Image Name
                        $imageName = rand(111,99999).'.'.$extension;
                        $imagePath = 'account/user/images/'.$imageName;
                        // Upload the Image
                        Image::make($image_tmp)->save($imagePath);

                        $oldImage = $user->photo;

                        if(!empty($oldImage)){
                            if(file_exists('account/user/images/'.$oldImage)){
                                unlink('account/user/images/'.$oldImage);
                            }
                        }
                    }
                }else if(!empty($user->image)){
                    $imageName = $user->image;
                }else{
                    $imageName = "";
                }

                $user->firstname = $request->firstname;
                $user->lastname = $request->lastname;
                $user->phone = $request->phone;
                $user->photo = $imageName;
                $user->password = Hash::make($request->password);
                $user->status = $request->status;
                    
                $user->save();

                return response()->json([
                    'status' => true,
                    'code' => 'USER_UPDATED',
                    'message' => 'User updated Successfully!'
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
     * Show account Infos
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function showUserAccount(Request $request)
    {
        try {
            $user = $request->user();
            return response()->json([
                'status' => true,
                'code' => 'USER_SHOWED',
                'data' => [
                    'user' => $user,
                ]
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
     * Update User Status
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateUserStatus(Request $request,$id){

        try {
            if(!$request->user()->can('users_update')){
                return response()->json([
                    'status' => false,
                    'code' => 'NOT_ALLOWED',
                    'message' => 'You Dont Have Access To See Update Users',
                    ],
                    405);
            }
            $user = User::find($id);

            //test if status false or true
            if($request->status == 'true'){
                $userStatus = 1; 
            }else{
                $userStatus = 0;
            }

            $user->status = $userStatus;
            $user->save();

            return response()->json([
                'status' => true,
                'code' => 'STATUS_USER_UPDATED',
                'message' => 'User Status Updated Successfully!',
                'data' => $user->status
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

}
