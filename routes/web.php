<?php

use Illuminate\Support\Facades\Route;

Route::redirect("/", "/dashboards/home");

Route::view("api-docs/v1", "redoc");
