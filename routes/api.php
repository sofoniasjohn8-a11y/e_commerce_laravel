<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\BrandController;

Route::post('/admin/login',[AuthController::class,'authenticate']);
// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::group(['middleware'=>'auth:sanctum'],function(){
    // Route::get('/categories',[CategoryController::class,'index']);
    // Route::post('/categories',[CategoryController::class,'store']);
    // Route::get('/categories/{id}',[CategoryController::class,'show']);
    //  Route::put('/categories/{id}',[CategoryController::class,'update']);
    //  Route::delete('/categories/{id}',[CategoryController::class,'destroy']);

    Route::resource('categories',CategoryController::class);
    Route::resource('brands',BrandController::class);
});
