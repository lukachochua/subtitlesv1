<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('stores a validated MP4 using a generated path', function () {
    Storage::fake('local');

    $video = UploadedFile::fake()->create(
        'ქართული-ინტერვიუ.mp4',
        100,
        'video/mp4',
    );

    $response = $this->post(route('video-projects.store'), [
        'video' => $video,
    ]);

    $response->assertRedirectToRoute('home')
        ->assertSessionHasNoErrors();

    $storedFiles = Storage::disk('local')->allFiles('video-projects');

    expect($storedFiles)->toHaveCount(1)
        ->and($storedFiles[0])->not->toContain('ქართული-ინტერვიუ');

    Storage::disk('local')->assertExists($storedFiles[0]);
});
