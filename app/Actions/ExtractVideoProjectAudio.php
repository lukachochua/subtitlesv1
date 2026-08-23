<?php

namespace App\Actions;

use App\Models\VideoProject;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class ExtractVideoProjectAudio
{
    public function handle(VideoProject $videoProject): string
    {
        $disk = Storage::disk($videoProject->disk);

        if (! $disk->exists($videoProject->path)) {
            throw new RuntimeException('The video project file does not exist.');
        }

        $audioPath = "video-projects/{$videoProject->id}/audio.wav";

        $disk->makeDirectory(dirname($audioPath));
        $disk->delete($audioPath);

        try {
            $result = Process::timeout(120)->run([
                '/usr/bin/ffmpeg',
                '-nostdin',
                '-hide_banner',
                '-loglevel',
                'error',
                '-y',
                '-i',
                $disk->path($videoProject->path),
                '-vn',
                '-ac',
                '1',
                '-ar',
                '16000',
                '-c:a',
                'pcm_s16le',
                $disk->path($audioPath),
            ]);

            $result->throw();

            if (! $disk->exists($audioPath) || $disk->size($audioPath) === 0) {
                throw new RuntimeException('FFmpeg did not create a usable audio file.');
            }
        } catch (Throwable $exception) {
            $disk->delete($audioPath);

            throw $exception;
        }

        return $audioPath;
    }
}
