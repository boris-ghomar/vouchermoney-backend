<?php

use Illuminate\Support\Facades\Route;

Route::get("/", function () {
    return redirect("/dashboards/home");
});

Route::get('api-docs', function () {
    return view('redoc');
});
