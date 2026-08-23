<?php

namespace App\Actions;

use App\Models\VideoProject;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use JsonException;
use RuntimeException;
use UnexpectedValueException;

class InspectVideoProject
{
    public function handle(VideoProject $videoProject): VideoProject
    {
        $disk = Storage::disk($videoProject->disk);

        if (! $disk->exists($videoProject->path)) {
            throw new RuntimeException('The video project file does not exist.');
        }

        $result = Process::timeout(30)->run([
            '/usr/bin/ffprobe',
            '-v',
            'error',
            '-show_entries',
            'format=duration:stream=codec_type',
            '-of',
            'json',
            $disk->path($videoProject->path),
        ]);

        $result->throw();

        try {
            $inspection = json_decode($result->output(), true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new UnexpectedValueException('ffprobe returned invalid JSON.', previous: $exception);
        }

        if (! is_array($inspection)) {
            throw new UnexpectedValueException('ffprobe returned an invalid result.');
        }

        $durationSeconds = $inspection['format']['duration'] ?? null;

        if (! is_numeric($durationSeconds) || (float) $durationSeconds <= 0) {
            throw new UnexpectedValueException('ffprobe did not return a positive container duration.');
        }

        $hasVideo = false;
        $hasAudio = false;
        $streams = $inspection['streams'] ?? null;

        if (! is_array($streams)) {
            throw new UnexpectedValueException('ffprobe did not return media streams.');
        }

        foreach ($streams as $stream) {
            if (! is_array($stream)) {
                continue;
            }

            $hasVideo = $hasVideo || ($stream['codec_type'] ?? null) === 'video';
            $hasAudio = $hasAudio || ($stream['codec_type'] ?? null) === 'audio';
        }

        if (! $hasVideo || ! $hasAudio) {
            throw new UnexpectedValueException('The video project must contain video and audio streams.');
        }

        $videoProject->update([
            'duration_ms' => (int) round((float) $durationSeconds * 1_000),
        ]);

        return $videoProject->refresh();
    }
}
