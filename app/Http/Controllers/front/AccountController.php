<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Order;
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
    public function getOrderDetails($id,Request $request){
            $order = Order::where([
                                'user_id'=>$request->user()->id,
                                'id' => $id
                            ])
                            ->with('items')
                            ->first();
            if($order == null){
                return response()->json([
                    'status' => 404,
                    'meessage' => 'Order not Found',
                    'data' => []
                ],404);
            }
            else{
                return response()->json([
                    'status' => 200,
                    'data' => $order
                ],200);
            }
    }
    public function getOrders(Request  $request){
        $orders = Order::where('user_id',$request->user()->id)->get();

        return response()->json([
            'status' => 200,
            'data' => $orders
        ],200);
    }
        public function getOrderDetail($id)
       {
          // Use first() to get a single object or null
                    $order = Order::with('items','items.product')->find($id);

         // Now this check will work correctly
                if (!$order) {
                    return response()->json([
                        'data' => null,
                        'message' => "Order not Found",
                        'status' => 404 // Use 404 for "Not Found"
                    ], 404);
                }

                return response()->json([
                    'data' => $order,
                    'status' => 200
                ], 200);
            }
    public function updateProfile(Request $request){
        $user = User::find($request->user()->id);
        if($user == null){
            response()->json([
                'status'=> 400,
                'message'=>'User is Not Found'
            ],400);
        }

        $rules = [
            'name'             => 'required',
            'email'             =>'required|unique:users,email,' . $user->id,
            'address'       => 'required',
            'state' => 'required',
            'zip'       => 'required',
            'city'       => 'required',
            'mobile'       => 'required'
        ];
        
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()
            ], 400);
        }
        $user->name=$request->name;
        $user->email=$request->email;
        $user->address=$request->address;
        $user->state=$request->state;
        $user->city=$request->city;
        $user->mobile=$request->mobile;
        $user->zip=$request->zip;
        $user->save();

        return response()->json([
            'status'=> 200,
            'data' => $user,
            'message' => "You have Updated your Credentials Successfully"
        ],200);

    }
    public function getProfileDetails(Request $request){
        $user = User::find($request->user()->id);

         if($user == null){
            response()->json([
                'status'=> 400,
                'message'=>'User is Not Found'
            ],400);
        }

        return response()->json([
            'status' => 200,
            'data'  => $user
        ],200);
    }
}
