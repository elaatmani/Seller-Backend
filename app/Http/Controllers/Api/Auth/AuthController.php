<?php

namespace App\Http\Controllers\Api\Auth;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AuthController extends Controller
{

    
    /** 
     * Create User
     * @param Request $request
     * @return User
    */
    public function createUser(Request $request)
    {
        try{    
       
                if(!$request->user()->can('users_create')){
                    return response()->json([
                    'status' => false,
                    'code' => 'NOT_ALLOWED',
                    'message' => 'You Dont Have Access To Create User',
                    ],
                    405);
                }
                //Validated
                $validateUser = Validator::make($request->all(),
                [
                'firstname' => 'required',
                'lastname' => 'required',
                'phone' => 'required',
                'email' => 'required|email|unique:users,email',
                'password' => 'required',
                'status' => 'required'
                ]);
               
                if($validateUser->fails()){
                    return response()->json([
                        'status' => false,
                        'code' => 'VALIDATION_ERROR',
                        'message' => 'validation error',
                        'error' => $validateUser->errors()],
                        401);
                }


                $user = User::create([
                    'firstname' => $request->firstname,
                    'lastname' => $request->lastname,
                    'phone' => $request->phone,
                    'email' => $request->email, 
                    'password' => Hash::make($request->password),
                    'status' => $request->status,
                ]);

                $role = Role::where('id', $request->role)->value('name');
                $user->assignRole($role);

                // $user->assignRole('agente');

                return response()->json([
                    'status' => true,
                    'code' => 'USER_CREATED',
                    'message' => 'User Created Successfully!',
                    'token' => $user ->createToken("API TOKEN")->plainTextToken],
                    200);

        }catch(\Throwable $th){
            return response()->json([
                'status' => false,
                'code' => 'SERVER_ERROR',
                'message' => $th->getMessage(),
                'error' => $validateUser->errors()],
                500);
        }
        
    }

    /**
     * Login User
     * @param Request $request
     * @return User
     */
    public function loginUser(Request $request)
    {
        try {
           //Validated
           $validateUser = Validator::make($request->all(),
           [
           'email' => 'required|email',
           'password' => 'required'
           ]);
   
           if($validateUser->fails()){
               return response()->json([
                   'status' => false,
                   'message' => 'validation error',
                   'code' => 'VALIDATION_ERROR',
                   'error' => $validateUser->errors()],
                   401);
           }

           if(!Auth::attempt($request->only(['email','password']))){
                return response()->json([
                    'status' => false,
                    'code' => 'INVALID_CREDENTIALS',
                    'message' => 'Email & Password does not match with our record.',
                ], 401);
           }

           $user = User::where([['email',$request->email],['status',1]])->first();
           if(isset($user)){


                 $role = Role::findOrFail($user->roles->first()->id);
                 $groupsWithRoles = $role->getPermissionNames();


                return response()->json([
                    'status' => true,
                    'message' => 'User Logged In Successfully!',
                    'code' => 'AUTHENTICATION_SUCCESSFUL',
                    'data' =>[ 'token' => $user ->createToken("API TOKEN")->plainTextToken,
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
                                
                              
                ]],
                    200);
            }else{
                return response()->json([
                    'status' => false,
                    'code' => 'NOT_ACTIVE_ERROR',
                    'message' => 'You dont Have Access anymore!',
                     ],
                    405);
            }

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
                'code' => 'SERVER_ERROR',
                'error' => $validateUser->errors()],
                500);
        }
    }

     /**
     * Logout User
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
            
          return response()->json([
            'status' => true,
            'code' => 'LOGOUT_SUCCESSFUL',
            'message' => 'User Logged Out Successfully!',
             ],
            200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'code' => 'SERVER_ERROR',
                'message' => $th->getMessage()],
                500);
        }
    }

    /**
     * Permission User
     * @param Request $request
     * @return User
     */
    public function userPermission(Request $request){
        $role = Role::findOrFail($request->user()->roles->first()->id);
        $groupsWithRoles = $role->getPermissionNames();
        return response()->json([
            'status' => true,
            'code' => 'SUCCESS',
            'data' => [
                'permissions' => $groupsWithRoles,
            ],
             ],
            200); 
    }

}
