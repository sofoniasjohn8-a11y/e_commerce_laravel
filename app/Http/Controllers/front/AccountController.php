<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth; 

class AccountController extends Controller
{
    public  function register(Request $request){
        $rules = [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required',
        ];
        $validator = Validator::make($request->all(),$rules);
        if($validator->fails()){
            return response()->json([
                'status' => 400,
                'error' => $validator->errors()
            ],400);
        }

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->role = 'customer';
        $user->save();

    return response()->json([
                'status' => 200,
                'message' => 'You Have Registered Succesfully'
            ],200);
    }
     public function authenticate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([ // 5. Added () after response
                'status' => 400,
                'errors' => $validator->errors()
            ], 400);
        }

        // 6. Changed 'emails' to 'email' and added Auth check
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            
            $user = Auth::user(); // 7. Simplified getting the user

            $token = $user->createToken('token')->plainTextToken;
                return response()->json([
                    'status' => 200,
                    'token' => $token,
                    'id' => $user->id,
                    'name' => $user->name
                ], 200);
        } else {
            return response()->json([
                'status' => 401,
                'message' => 'Either Email or Password is incorrect' // 8. Fixed arrow syntax
            ], 401);
        }
    }
}
