<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use \App\Http\Controllers\CustomerController;

Route::middleware("auth:sanctum")->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::prefix('customers')->controller(CustomerController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/{customer}', 'show');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'destroy');
        Route::put('/{id}/deactivate','deactivateCustomer');
        Route::post('/{customerId}/attach-user','attachUserToCustomer');


    });

//    Route::resource('customers', CustomerController::class)
//        ->except(['create', 'edit']);

    Route::post('logout',[AuthController::class,'logout']);
});


Route::post('register',[AuthController::class,'register']);
Route::post('login',[AuthController::class,'login'])->name('login');

