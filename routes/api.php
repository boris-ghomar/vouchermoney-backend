<?php

use App\Http\Middleware\CheckCustomerApiToken;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiVoucherController;

//Route::middleware("auth:sanctum")->group(function () {
//    Route::get('/user', function (Request $request) {
//        return $request->user();
//    });
//
//    Route::post('logout',[AuthController::class,'logout']);
//});
//
//
//Route::post('register',[AuthController::class,'register']);
//Route::post('login',[AuthController::class,'login'])->name('login');

Route::middleware(CheckCustomerApiToken::class)->prefix('v1')->controller(ApiVoucherController::class)->group(function () {
    Route::get('vouchers', 'index');
    Route::post('vouchers', 'create');
    Route::put('vouchers/redeem', 'redeem'); // request - code
    Route::put('vouchers/freeze', 'freeze'); // request - code
    Route::put('vouchers/unfreeze', 'unfreeze'); // request - code
});
