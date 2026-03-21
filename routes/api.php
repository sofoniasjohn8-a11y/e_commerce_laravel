<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\SizeController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\TempImageController; 
use App\Http\Controllers\front\productController  as frontProductController;


Route::post('/admin/login',[AuthController::class,'authenticate']);
Route::get('products/get-latest',[frontProductController::class,'latest']);
Route::get('products/get-featured',[frontProductController::class,'featured']);
Route::get('/get-categories',[frontProductController::class,'getCategories']);
Route::get('/get-brands',[frontProductController::class,'getBrands']);
Route::get('/get-products',[frontProductController::class,'getProducts']);
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
    Route::get('/sizes',[SizeController::class,'index']);
    Route::resource('products',ProductController::class);
    Route::put('products/setDDefaultImage/{id}',[ProductController::class,'setDefaultImage']);
    Route::delete('products/removeImage/{id}',[ProductController::class,'removeImage']);
    // Route::post('/temp-images',[TempImageController::class,'store']);
    // Route::delete('/temp-images/{id}',[TempImageController::class,'destroy']);
    Route::resource('temp-images',TempImageController::class);
});
