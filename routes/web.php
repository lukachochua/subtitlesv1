<?php

use App\Http\Controllers\StoreVideoProjectController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::post('/video-projects', StoreVideoProjectController::class)
    ->name('video-projects.store');
