<?php

use App\Models\VideoProject;
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

    $response->assertSessionHasNoErrors();

    $storedFiles = Storage::disk('local')->allFiles('video-projects');

    expect($storedFiles)->toHaveCount(1)
        ->and($storedFiles[0])->not->toContain('ქართული-ინტერვიუ');

    Storage::disk('local')->assertExists($storedFiles[0]);

    $videoProject = VideoProject::sole();

    $response->assertRedirectToRoute('video-projects.show', $videoProject);

    expect($videoProject->original_filename)->toBe('ქართული-ინტერვიუ.mp4')
        ->and($videoProject->disk)->toBe('local')
        ->and($videoProject->path)->toBe($storedFiles[0])
        ->and($videoProject->mime_type)->toBe('video/mp4')
        ->and($videoProject->size_bytes)->toBe($video->getSize());
});

test('removes the stored MP4 when metadata persistence fails', function () {
    Storage::fake('local');
    $this->withoutExceptionHandling();

    $video = UploadedFile::fake()->create('interview.mp4', 100, 'video/mp4');
    $path = 'video-projects/'.$video->hashName();

    VideoProject::creating(function (): never {
        throw new RuntimeException('Simulated persistence failure.');
    });

    expect(fn () => $this->post(route('video-projects.store'), [
        'video' => $video,
    ]))->toThrow(RuntimeException::class, 'Simulated persistence failure.');

    Storage::disk('local')->assertMissing($path);
    expect(VideoProject::query()->count())->toBe(0);
});
