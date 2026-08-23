<?php

namespace App\Actions;

use App\Models\CaptionCue;
use App\Models\VideoProject;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use JsonException;
use RuntimeException;
use UnexpectedValueException;

class GenerateVideoProjectAssFile
{
    public function __construct(private GenerateAssSubtitleContent $generateAssSubtitleContent) {}

    public function handle(VideoProject $videoProject): string
    {
        $disk = Storage::disk($videoProject->disk);

        if (! $disk->exists($videoProject->path)) {
            throw new RuntimeException('The video project file does not exist.');
        }

        $captionCues = CaptionCue::query()
            ->whereBelongsTo($videoProject)
            ->orderBy('order')
            ->get();

        if ($captionCues->isEmpty()) {
            throw new RuntimeException('The video project has no saved caption cues.');
        }

        $result = Process::timeout(30)->run([
            '/usr/bin/ffprobe',
            '-v',
            'error',
            '-select_streams',
            'v:0',
            '-show_entries',
            'stream=width,height',
            '-of',
            'json',
            $disk->path($videoProject->path),
        ]);

        $result->throw();
        [$sourceWidth, $sourceHeight] = $this->dimensions($result->output());
        $assContent = $this->generateAssSubtitleContent->handle(
            $captionCues,
            $videoProject->resolvedCaptionStyle(),
            $sourceWidth,
            $sourceHeight,
        );
        $assPath = "video-projects/{$videoProject->id}/captions.ass";

        $disk->makeDirectory(dirname($assPath));

        if (! $disk->put($assPath, $assContent)) {
            throw new RuntimeException('The ASS subtitle file could not be written.');
        }

        return $assPath;
    }

    /**
     * @return array{int, int}
     */
    private function dimensions(string $ffprobeOutput): array
    {
        try {
            $inspection = json_decode($ffprobeOutput, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new UnexpectedValueException('ffprobe returned invalid JSON.', previous: $exception);
        }

        $stream = $inspection['streams'][0] ?? null;
        $width = is_array($stream) ? ($stream['width'] ?? null) : null;
        $height = is_array($stream) ? ($stream['height'] ?? null) : null;

        if (! is_int($width) || ! is_int($height) || $width < 1 || $height < 1) {
            throw new UnexpectedValueException('ffprobe did not return positive video dimensions.');
        }

        return [$width, $height];
    }
}
