<?php

use App\Http\Controllers\DownloadVideoProjectCaptionedVideoController;
use App\Http\Controllers\MergeCaptionCueWithNextController;
use App\Http\Controllers\RenderVideoProjectCaptionedVideoController;
use App\Http\Controllers\ShowVideoProjectController;
use App\Http\Controllers\ShowVideoProjectMediaController;
use App\Http\Controllers\SplitCaptionCueController;
use App\Http\Controllers\StoreVideoProjectController;
use App\Http\Controllers\UpdateCaptionCueEndTimeController;
use App\Http\Controllers\UpdateCaptionCueStartTimeController;
use App\Http\Controllers\UpdateCaptionCueTextController;
use App\Http\Controllers\UpdateVideoProjectCaptionStyleController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::get('/video-projects/{videoProject}', ShowVideoProjectController::class)
    ->name('video-projects.show');

Route::get('/video-projects/{videoProject}/media', ShowVideoProjectMediaController::class)
    ->name('video-projects.media');

Route::get(
    '/video-projects/{videoProject}/export',
    DownloadVideoProjectCaptionedVideoController::class,
)->name('video-projects.export.show');

Route::post(
    '/video-projects/{videoProject}/render',
    RenderVideoProjectCaptionedVideoController::class,
)->name('video-projects.render.store');

Route::post('/video-projects', StoreVideoProjectController::class)
    ->name('video-projects.store');

Route::patch(
    '/video-projects/{videoProject}/caption-style',
    UpdateVideoProjectCaptionStyleController::class,
)->name('video-projects.caption-style.update');

Route::patch(
    '/video-projects/{videoProject}/caption-cues/{captionCue}',
    UpdateCaptionCueTextController::class,
)
    ->scopeBindings()
    ->name('video-projects.caption-cues.update');

Route::post(
    '/video-projects/{videoProject}/caption-cues/{captionCue}/split',
    SplitCaptionCueController::class,
)
    ->scopeBindings()
    ->name('video-projects.caption-cues.split.store');

Route::post(
    '/video-projects/{videoProject}/caption-cues/{captionCue}/merge-next',
    MergeCaptionCueWithNextController::class,
)
    ->scopeBindings()
    ->name('video-projects.caption-cues.merge-next.store');

Route::patch(
    '/video-projects/{videoProject}/caption-cues/{captionCue}/end-time',
    UpdateCaptionCueEndTimeController::class,
)
    ->scopeBindings()
    ->name('video-projects.caption-cues.end-time.update');

Route::patch(
    '/video-projects/{videoProject}/caption-cues/{captionCue}/start-time',
    UpdateCaptionCueStartTimeController::class,
)
    ->scopeBindings()
    ->name('video-projects.caption-cues.start-time.update');
