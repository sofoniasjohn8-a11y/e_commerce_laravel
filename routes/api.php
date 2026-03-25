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
use App\Http\Controllers\front\AccountController;
use App\Http\Controllers\front\OrderController;


Route::post('/admin/login',[AuthController::class,'authenticate']);
Route::get('products/get-latest',[frontProductController::class,'latest']);
Route::get('products/get-featured',[frontProductController::class,'featured']);
Route::get('/get-categories',[frontProductController::class,'getCategories']);
Route::get('/get-brands',[frontProductController::class,'getBrands']);
Route::get('/get-products',[frontProductController::class,'getProducts']);
Route::get('/get-product/{id}',[frontProductController::class,'getProduct']);
Route::post('/register',[AccountController::class,'register']);
Route::post('/login',[AccountController::class,'authenticate']);



Route::group(['middleware'=>['auth:sanctum','checkUserRole']],function(){
    Route::post('/save_product',[OrderController::class,'saveOrder']);
});
Route::group(['middleware'=>['auth:sanctum','checkAdminRole']],function(){
  
    Route::resource('categories',CategoryController::class);
    Route::resource('brands',BrandController::class);
    Route::get('/sizes',[SizeController::class,'index']);
    Route::resource('products',ProductController::class);
    Route::put('products/setDDefaultImage/{id}',[ProductController::class,'setDefaultImage']);
    Route::delete('products/removeImage/{id}',[ProductController::class,'removeImage']);
    Route::resource('temp-images',TempImageController::class);
});
