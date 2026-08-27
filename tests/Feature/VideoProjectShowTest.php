<?php

use App\Enums\VideoRenderStatus;
use App\Models\VideoProject;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

test('displays safe uploaded video metadata', function () {
    $videoProject = VideoProject::create([
        'original_filename' => 'ქართული-ინტერვიუ.mp4',
        'disk' => 'local',
        'path' => 'video-projects/private-source.mp4',
        'mime_type' => 'video/mp4',
        'size_bytes' => 48_392_017,
        'duration_ms' => 7_967,
    ]);

    $this->get(route('video-projects.show', $videoProject))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('VideoProjects/Show')
            ->where('videoProject', [
                'id' => $videoProject->id,
                'original_filename' => 'ქართული-ინტერვიუ.mp4',
                'mime_type' => 'video/mp4',
                'size_bytes' => 48_392_017,
                'duration_ms' => 7_967,
            ])
            ->where('cues', null)
            ->where('captionStyle', VideoProject::DEFAULT_CAPTION_STYLE)
            ->where('renderQuality', 'high')
            ->where('renderState', [
                'status' => null,
                'error' => null,
                'rendered_at' => null,
            ])
            ->where('transcriptionState', [
                'status' => null,
                'error' => null,
                'transcribed_at' => null,
            ])
            ->where('hasCaptionedVideo', false)
            ->missing('videoProject.disk')
            ->missing('videoProject.path'));
});

test('reports when a completed captioned video is available', function () {
    Storage::fake('local');
    $videoProject = VideoProject::create([
        'original_filename' => 'exported.mp4',
        'disk' => 'local',
        'path' => 'video-projects/exported.mp4',
        'mime_type' => 'video/mp4',
        'size_bytes' => 2_048,
        'render_status' => VideoRenderStatus::Completed,
        'rendered_at' => '2026-08-23 20:00:00',
    ]);
    Storage::disk('local')->put(
        "video-projects/{$videoProject->id}/captioned.mp4",
        'captioned-video',
    );

    $this->get(route('video-projects.show', $videoProject))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('renderState', [
                'status' => 'completed',
                'error' => null,
                'rendered_at' => $videoProject->rendered_at?->toIso8601String(),
            ])
            ->where('hasCaptionedVideo', true));
});

test('exposes a safe failed render state while retaining an older export', function () {
    Storage::fake('local');
    $videoProject = VideoProject::create([
        'original_filename' => 'failed-export.mp4',
        'disk' => 'local',
        'path' => 'video-projects/failed-export.mp4',
        'mime_type' => 'video/mp4',
        'size_bytes' => 2_048,
        'render_status' => VideoRenderStatus::Failed,
        'render_error' => 'The captioned video could not be exported. Check the media files and try again.',
        'rendered_at' => '2026-08-22 18:30:00',
    ]);
    Storage::disk('local')->put(
        "video-projects/{$videoProject->id}/captioned.mp4",
        'older-captioned-video',
    );

    $this->get(route('video-projects.show', $videoProject))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('renderState', [
                'status' => 'failed',
                'error' => 'The captioned video could not be exported. Check the media files and try again.',
                'rendered_at' => $videoProject->rendered_at?->toIso8601String(),
            ])
            ->where('hasCaptionedVideo', true));
});

test('exposes a stored caption style instead of the default', function () {
    $captionStyle = [
        ...VideoProject::DEFAULT_CAPTION_STYLE,
        'font' => 'georgian_serif',
        'font_size_px' => 42,
        'vertical_position_percent' => 35,
        'shadow' => false,
    ];
    $videoProject = VideoProject::create([
        'original_filename' => 'styled.mp4',
        'disk' => 'local',
        'path' => 'video-projects/styled.mp4',
        'mime_type' => 'video/mp4',
        'size_bytes' => 2_048,
        'caption_style' => $captionStyle,
    ]);

    $this->get(route('video-projects.show', $videoProject))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('captionStyle', $captionStyle));
});

test('exposes transient generated cues when a NeMo result exists', function () {
    Storage::fake('local');
    $videoProject = VideoProject::create([
        'original_filename' => 'ქართული-ინტერვიუ.mp4',
        'disk' => 'local',
        'path' => 'video-projects/private-source.mp4',
        'mime_type' => 'video/mp4',
        'size_bytes' => 48_392_017,
        'duration_ms' => 2886,
    ]);
    $fixture = file_get_contents(base_path('tests/Fixtures/nemo-transcription.json'));

    if ($fixture === false) {
        throw new RuntimeException('The NeMo transcription fixture could not be read.');
    }

    Storage::disk('local')->put(
        "video-projects/{$videoProject->id}/transcription.nemo-fastconformer.raw.json",
        $fixture,
    );

    $this->get(route('video-projects.show', $videoProject))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('cues', [[
                'id' => null,
                'order' => 1,
                'text' => 'ერთი ორი, გამარჯობა.',
                'start_ms' => 160,
                'end_ms' => 2886,
                'words' => [
                    ['order' => 1, 'text' => 'ერთი', 'start_ms' => 160, 'end_ms' => 240],
                    ['order' => 2, 'text' => 'ორი,', 'start_ms' => 640, 'end_ms' => 960],
                    ['order' => 3, 'text' => 'გამარჯობა.', 'start_ms' => 1_600, 'end_ms' => 2_886],
                ],
            ]]));
});

test('prefers saved cues over the NeMo transcription result', function () {
    Storage::fake('local');
    $videoProject = VideoProject::create([
        'original_filename' => 'ქართული-ინტერვიუ.mp4',
        'disk' => 'local',
        'path' => 'video-projects/saved-cues-source.mp4',
        'mime_type' => 'video/mp4',
        'size_bytes' => 48_392_017,
        'duration_ms' => 2_886,
    ]);
    $savedCue = $videoProject->captionCues()->create([
        'order' => 1,
        'text' => 'ხელით შესწორებული ტექსტი',
        'start_ms' => 200,
        'end_ms' => 2_400,
    ]);
    Storage::disk('local')->put(
        "video-projects/{$videoProject->id}/transcription.nemo-fastconformer.raw.json",
        '{invalid-json',
    );

    $this->get(route('video-projects.show', $videoProject))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('cues', [[
                'id' => $savedCue->id,
                'order' => 1,
                'text' => 'ხელით შესწორებული ტექსტი',
                'start_ms' => 200,
                'end_ms' => 2_400,
                'words' => [],
            ]]));
});

test('uses original word timings with corrected text for legacy saved cues', function () {
    Storage::fake('local');
    $videoProject = VideoProject::create([
        'original_filename' => 'legacy.mp4',
        'disk' => 'local',
        'path' => 'video-projects/legacy/source.mp4',
        'mime_type' => 'video/mp4',
        'size_bytes' => 2_048,
        'duration_ms' => 2_886,
    ]);
    $savedCue = $videoProject->captionCues()->create([
        'order' => 1,
        'text' => 'სწორი ორი, გამარჯობა.',
        'start_ms' => 160,
        'end_ms' => 2_886,
    ]);
    Storage::disk('local')->put(
        "video-projects/{$videoProject->id}/transcription.nemo-fastconformer.raw.json",
        file_get_contents(base_path('tests/Fixtures/nemo-transcription.json')),
    );

    $this->get(route('video-projects.show', $videoProject))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('cues.0.id', $savedCue->id)
            ->where('cues.0.words', [
                ['order' => 1, 'text' => 'სწორი', 'start_ms' => 160, 'end_ms' => 240],
                ['order' => 2, 'text' => 'ორი,', 'start_ms' => 640, 'end_ms' => 960],
                ['order' => 3, 'text' => 'გამარჯობა.', 'start_ms' => 1_600, 'end_ms' => 2_886],
            ]));
});

test('does not hide malformed existing transcription data as an absent result', function () {
    Storage::fake('local');
    $videoProject = VideoProject::create([
        'original_filename' => 'ქართული-ინტერვიუ.mp4',
        'disk' => 'local',
        'path' => 'video-projects/private-source.mp4',
        'mime_type' => 'video/mp4',
        'size_bytes' => 48_392_017,
        'duration_ms' => 2886,
    ]);
    Storage::disk('local')->put(
        "video-projects/{$videoProject->id}/transcription.nemo-fastconformer.raw.json",
        '{invalid-json',
    );

    $this->get(route('video-projects.show', $videoProject))->assertServerError();
});

test('exposes an explicit null duration before inspection', function () {
    $videoProject = VideoProject::create([
        'original_filename' => 'uninspected.mp4',
        'disk' => 'local',
        'path' => 'video-projects/uninspected.mp4',
        'mime_type' => 'video/mp4',
        'size_bytes' => 1_024,
    ]);

    $this->get(route('video-projects.show', $videoProject))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('videoProject.duration_ms', null));
});

test('returns not found for a missing video project', function () {
    $this->get(route('video-projects.show', 999))->assertNotFound();
});
