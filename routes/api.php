<?php

use App\Http\Middleware\CheckCustomerApiToken;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiVoucherController;
use App\Models\Permission;

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
//    Route::get('vouchers', 'index')->can(Permission::CUSTOMER_VOUCHER_VIEW);
    Route::post('vouchers/generate', 'create')->can(Permission::CUSTOMER_VOUCHER_GENERATE);
    Route::post('vouchers/redeem', 'redeem')->can(Permission::CUSTOMER_VOUCHER_REDEEM);
    Route::put('vouchers/freeze', 'freeze')->can(Permission::CUSTOMER_VOUCHER_FREEZE);
    Route::put('vouchers/activate', 'activate')->can(Permission::CUSTOMER_VOUCHER_FREEZE);
});
