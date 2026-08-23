<?php

namespace App\Actions;

use App\Models\VideoProject;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use JsonException;
use RuntimeException;
use Throwable;
use UnexpectedValueException;

class TranscribeVideoProjectAudioWithNemo
{
    public function handle(VideoProject $videoProject, string $audioPath): string
    {
        $pythonPath = config('services.nemo_asr.python');

        if (! is_string($pythonPath) || ! is_file($pythonPath) || ! is_executable($pythonPath)) {
            throw new RuntimeException('The configured NeMo Python executable is unavailable.');
        }

        $processPath = config('services.nemo_asr.process_path');

        if (! is_string($processPath) || $processPath === '') {
            throw new RuntimeException('The configured NeMo process PATH is unavailable.');
        }

        $scriptPath = base_path('transcribe_nemo.py');

        if (! is_file($scriptPath)) {
            throw new RuntimeException('The NeMo transcription script is unavailable.');
        }

        $disk = Storage::disk($videoProject->disk);

        if (! $disk->exists($audioPath)) {
            throw new RuntimeException('The extracted audio file does not exist.');
        }

        $outputDirectory = "video-projects/{$videoProject->id}";
        $pendingPath = "{$outputDirectory}/transcription.nemo-fastconformer.processing.json";
        $completedPath = "{$outputDirectory}/transcription.nemo-fastconformer.raw.json";
        $disk->makeDirectory($outputDirectory);
        $disk->delete($pendingPath);

        try {
            $result = Process::env(['PATH' => $processPath])
                ->timeout(3_600)
                ->run([
                    $pythonPath,
                    $scriptPath,
                    $disk->path($audioPath),
                    $disk->path($pendingPath),
                ]);

            $result->throw();

            if (! $disk->exists($pendingPath) || $disk->size($pendingPath) === 0) {
                throw new RuntimeException('NeMo did not create a transcription result.');
            }

            $this->validateResult($disk->get($pendingPath));
            $disk->delete($completedPath);

            if (! $disk->move($pendingPath, $completedPath)) {
                throw new RuntimeException('The NeMo transcription result could not be finalized.');
            }
        } catch (Throwable $exception) {
            $disk->delete($pendingPath);

            throw $exception;
        }

        return $completedPath;
    }

    private function validateResult(string $json): void
    {
        try {
            $result = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new UnexpectedValueException('NeMo returned invalid JSON.', previous: $exception);
        }

        if (! is_array($result) || ! is_array(data_get($result, 'timestamp.word')) || data_get($result, 'timestamp.word') === []) {
            throw new UnexpectedValueException('NeMo did not return usable word timestamps.');
        }
    }
}
