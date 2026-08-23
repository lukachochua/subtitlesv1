<?php

use App\Http\Requests\StoreVideoProjectRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as LaravelValidator;

/**
 * @param  array<string, mixed>  $data
 */
function videoProjectUploadValidator(array $data): LaravelValidator
{
    return Validator::make($data, (new StoreVideoProjectRequest)->rules());
}

test('accepts an MP4 within the development size limit', function () {
    $video = UploadedFile::fake()->create(
        'ქართული-ინტერვიუ.mp4',
        500_000,
        'video/mp4',
    );

    expect(videoProjectUploadValidator(['video' => $video])->passes())->toBeTrue();
});

test('requires a video file', function () {
    $validator = videoProjectUploadValidator([]);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('video'))->toBeTrue();
});

test('rejects a non-MP4 file', function () {
    $video = UploadedFile::fake()->create(
        'transcript.txt',
        100,
        'text/plain',
    );

    $validator = videoProjectUploadValidator(['video' => $video]);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('video'))->toBeTrue();
});

test('rejects an MP4 over the development size limit', function () {
    $video = UploadedFile::fake()->create(
        'large-video.mp4',
        500_001,
        'video/mp4',
    );

    $validator = videoProjectUploadValidator(['video' => $video]);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('video'))->toBeTrue();
});
