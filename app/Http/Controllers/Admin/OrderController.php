<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;

class OrderController extends Controller
{
    public function index(){
        $orders = Order::orderBy('created_at','DESC')
                    ->get();
        return response()->json([
            'data' => $orders,
            'status' => 200
        ],200);
    }
    public function show($id) {
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
public function update($id,Request $request){
    $order = Order::find($id);
    if($order == null){
       return  response()->json([
            'status' => 404,
            'message'=>'Order not Found'
        ],404);
    }
    $order->payment_status = $request->payment_status;
    $order->status = $request->status;
    $order->save();

   return response()->json([
            'status' => 200,
            'message'=>'Order has been updated succesfully'
        ],200);
}
    
    

}
