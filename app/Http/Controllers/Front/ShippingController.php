<?php

namespace App\Http\Controllers\front;

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
}
