<?php

use App\Http\Controllers\ShowVideoProjectController;
use App\Http\Controllers\StoreVideoProjectController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::get('/video-projects/{videoProject}', ShowVideoProjectController::class)
    ->name('video-projects.show');

Route::post('/video-projects', StoreVideoProjectController::class)
    ->name('video-projects.store');
