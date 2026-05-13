<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ShippingCharge;

class ShippingController extends Controller
{
    public function get_shipping(){
        $shipping = ShippingCharge::first();
        return response()->json([
            'status' => 200,
            'data' => $shipping
        ],200);
    }
    public function update_shipping(Request $request){
      
        $shipping = ShippingCharge::UpdateOrInsert([
            'id' => 1
        ],[
            'amount' => $request->amount
        ]);
        // if($shipping == null){
        //     $shipping = new ShippingCharge();
        //     $shipping->amount = $request->amount;
        //     $shipping->save();
        // }
        // else{
        //     $shipping->amount = $request->amount;
        //     $shipping->save();
        // }
        return response()->json([
            'status' => 200,
            'message' => 'Shipping Charge Updated Successfully'
        ],200);
    }
}
