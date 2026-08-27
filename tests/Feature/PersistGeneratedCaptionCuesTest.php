<?php

use App\Actions\PersistGeneratedCaptionCues;
use App\Models\VideoProject;
use App\ValueObjects\CaptionCue;
use App\ValueObjects\TranscriptionWord;

test('persists generated cues for a project without saved cues', function () {
    $videoProject = createVideoProjectForGeneratedCuePersistence();
    $generatedCues = [
        new CaptionCue(1, 'როგორ ხარ?', 480, 2_160, [
            new TranscriptionWord('როგორ', 480, 1_100),
            new TranscriptionWord('ხარ?', 1_160, 2_160),
        ]),
        new CaptionCue(2, 'კარგად ვარ.', 2_240, 3_840),
    ];

    $savedCues = app(PersistGeneratedCaptionCues::class)->handle(
        $videoProject,
        $generatedCues,
    );

    expect($savedCues)->toHaveCount(2)
        ->and($videoProject->captionCues()->get()->map->only([
            'order',
            'text',
            'start_ms',
            'end_ms',
        ])->all())->toBe([
            [
                'order' => 1,
                'text' => 'როგორ ხარ?',
                'start_ms' => 480,
                'end_ms' => 2_160,
            ],
            [
                'order' => 2,
                'text' => 'კარგად ვარ.',
                'start_ms' => 2_240,
                'end_ms' => 3_840,
            ],
        ])
        ->and($savedCues[0]->words()->get()->map->only(['order', 'text', 'start_ms', 'end_ms'])->all())->toBe([
            ['order' => 1, 'text' => 'როგორ', 'start_ms' => 480, 'end_ms' => 1_100],
            ['order' => 2, 'text' => 'ხარ?', 'start_ms' => 1_160, 'end_ms' => 2_160],
        ]);
});

test('refuses to overwrite saved caption cues', function () {
    $videoProject = createVideoProjectForGeneratedCuePersistence();
    $videoProject->captionCues()->create([
        'order' => 1,
        'text' => 'ხელით შესწორებული ტექსტი',
        'start_ms' => 480,
        'end_ms' => 2_160,
    ]);

    expect(fn () => app(PersistGeneratedCaptionCues::class)->handle(
        $videoProject,
        [new CaptionCue(1, 'თავიდან გენერირებული ტექსტი', 480, 2_160)],
    ))->toThrow(
        LogicException::class,
        "Video project {$videoProject->id} already has saved caption cues.",
    );

    expect($videoProject->captionCues()->pluck('text')->all())->toBe([
        'ხელით შესწორებული ტექსტი',
    ]);
});

test('requires at least one generated cue', function () {
    $videoProject = createVideoProjectForGeneratedCuePersistence();

    expect(fn () => app(PersistGeneratedCaptionCues::class)->handle($videoProject, []))
        ->toThrow(
            InvalidArgumentException::class,
            'At least one generated caption cue is required.',
        );
});

function createVideoProjectForGeneratedCuePersistence(): VideoProject
{
    return VideoProject::create([
        'original_filename' => 'ქართული-ინტერვიუ.mp4',
        'disk' => 'local',
        'path' => 'video-projects/generated-cue-persistence/source.mp4',
        'mime_type' => 'video/mp4',
        'size_bytes' => 2_048,
        'duration_ms' => 8_000,
    ]);
}
