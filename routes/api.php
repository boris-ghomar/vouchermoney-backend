<?php

use App\Http\Middleware\CheckCustomerApiToken;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiVoucherController;
use App\Models\Permission;

Route::middleware(CheckCustomerApiToken::class)->prefix('v1')->controller(ApiVoucherController::class)->group(function () {
    Route::get('vouchers/view', 'view')->can(Permission::CUSTOMER_VOUCHER_REDEEM);
    Route::post('vouchers/generate', 'generate')->can(Permission::CUSTOMER_VOUCHER_GENERATE);
    Route::post('vouchers/redeem', 'redeem')->can(Permission::CUSTOMER_VOUCHER_REDEEM);
});
