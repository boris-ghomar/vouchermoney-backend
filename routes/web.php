<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get("/", function (Request $request) {
    $user = $request->user();

    if (!$user)
        return redirect("/login");

    return redirect("/dashboards/home");
});
