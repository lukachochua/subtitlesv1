<?php

use App\Http\Controllers\ShowVideoProjectController;
use App\Http\Controllers\ShowVideoProjectMediaController;
use App\Http\Controllers\StoreVideoProjectController;
use App\Http\Controllers\UpdateCaptionCueStartTimeController;
use App\Http\Controllers\UpdateCaptionCueTextController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::get('/video-projects/{videoProject}', ShowVideoProjectController::class)
    ->name('video-projects.show');

Route::get('/video-projects/{videoProject}/media', ShowVideoProjectMediaController::class)
    ->name('video-projects.media');

Route::post('/video-projects', StoreVideoProjectController::class)
    ->name('video-projects.store');

Route::patch(
    '/video-projects/{videoProject}/caption-cues/{captionCue}',
    UpdateCaptionCueTextController::class,
)
    ->scopeBindings()
    ->name('video-projects.caption-cues.update');

Route::patch(
    '/video-projects/{videoProject}/caption-cues/{captionCue}/start-time',
    UpdateCaptionCueStartTimeController::class,
)
    ->scopeBindings()
    ->name('video-projects.caption-cues.start-time.update');
