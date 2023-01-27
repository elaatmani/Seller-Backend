<?php

namespace App\Http\Controllers\Api\Auth;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

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

                return response()->json([
                    'status' => true,
                    'message' => 'User Created Successfully!',
                    'token' => $user ->createToken("API TOKEN")->plainTextToken],
                    200);

        }catch(\Throwable $th){
            return response()->json([
                'status' => false,
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
                   'error' => $validateUser->errors()],
                   401);
           }

           if(!Auth::attempt($request->only(['email','password']))){
                return response()->json([
                    'status' => false,
                    'message' => 'Email & Password does not match with our record.',
                ], 401);
           }
           $user = User::where([['email',$request->email],['status',1]])->first();
           if(isset($user)){
                return response()->json([
                    'status' => true,
                    'message' => 'User Logged In Successfully!',
                    'token' => $user ->createToken("API TOKEN")->plainTextToken],
                    200);
            }else{
                return response()->json([
                    'status' => false,
                    'message' => 'You dont Have Access anymore!',
                     ],
                    500);
            }

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
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
            $request->user()->tokens()->delete();
          return response()->json([
            'status' => true,
            'message' => 'User Logged Out Successfully!',
             ],
            200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()],
                500);
        }
    }

}
