<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('admin.login');
});

Route::get('/admin', function () {
    return view('admin.dashboard.index');
});

Route::get('/api-info', function () {
    return view('api-info');
});
