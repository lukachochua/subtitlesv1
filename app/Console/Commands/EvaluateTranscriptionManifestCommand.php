<?php

namespace App\Console\Commands;

use App\Actions\EvaluateTranscription;
use App\Actions\LoadTranscriptionEvaluationManifest;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use JsonException;
use Throwable;
use UnexpectedValueException;

#[Signature('transcription:evaluate {manifest : Path to the evaluation manifest} {--output= : Result JSON path; defaults beside the manifest}')]
#[Description('Evaluate preserved NeMo transcripts against verified Georgian references')]
class EvaluateTranscriptionManifestCommand extends Command
{
    public function handle(
        LoadTranscriptionEvaluationManifest $loadManifest,
        EvaluateTranscription $evaluateTranscription,
    ): int {
        try {
            $manifestPath = (string) $this->argument('manifest');
            $manifest = $loadManifest->handle($manifestPath);
            $clipResults = [];

            foreach ($manifest['clips'] as $clip) {
                $nemoResult = $this->loadNemoResult($clip['nemo_result']);
                $evaluation = $evaluateTranscription->handle($clip['reference'], $nemoResult['text']);
                $clipResult = [
                    'id' => $clip['id'],
                    'category' => $clip['category'],
                    'audio' => $clip['audio'],
                    'nemo_result' => $clip['nemo_result'],
                    'notes' => $clip['notes'],
                    'metrics' => $evaluation->toArray(),
                ];

                if (isset($clip['audio_duration_seconds'])) {
                    $clipResult['audio_duration_seconds'] = $clip['audio_duration_seconds'];
                }

                if (isset($clip['manual_correction_seconds'], $clip['audio_duration_seconds'])) {
                    $clipResult['manual_correction_seconds'] = $clip['manual_correction_seconds'];
                    $clipResult['correction_seconds_per_audio_minute'] = $clip['manual_correction_seconds'] / ($clip['audio_duration_seconds'] / 60);
                }

                $clipResults[] = $clipResult;
            }

            $result = [
                'dataset_version' => $manifest['dataset_version'],
                'generated_at' => now()->toIso8601String(),
                'clips' => $clipResults,
                'summary' => $this->summarize($clipResults),
            ];
            $outputPath = $this->outputPath($manifestPath);
            $encoded = json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR).PHP_EOL;

            if (file_put_contents($outputPath, $encoded) === false) {
                throw new UnexpectedValueException("Could not write evaluation result: {$outputPath}");
            }

            $this->table(
                ['Clip', 'Category', 'WER', 'CER', 'S', 'I', 'D'],
                array_map(fn (array $clip): array => [
                    $clip['id'],
                    $clip['category'],
                    number_format($clip['metrics']['wer'] * 100, 2).'%',
                    number_format($clip['metrics']['cer'] * 100, 2).'%',
                    $clip['metrics']['substitutions'],
                    $clip['metrics']['insertions'],
                    $clip['metrics']['deletions'],
                ], $clipResults),
            );
            $this->info("Evaluation result written to {$outputPath}");

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }

    /** @return array{text: string} */
    private function loadNemoResult(string $path): array
    {
        if (! is_file($path)) {
            throw new UnexpectedValueException("NeMo result does not exist: {$path}");
        }

        try {
            $result = json_decode(file_get_contents($path) ?: '', true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new UnexpectedValueException("NeMo result contains invalid JSON: {$path}", previous: $exception);
        }

        if (! is_array($result) || ! is_string($result['text'] ?? null)) {
            throw new UnexpectedValueException("NeMo result requires transcript text: {$path}");
        }

        return ['text' => $result['text']];
    }

    /**
     * @param  list<array<string, mixed>>  $clips
     * @return array<string, int|float>
     */
    private function summarize(array $clips): array
    {
        $summary = [
            'clips' => count($clips),
            'reference_words' => 0,
            'reference_characters' => 0,
            'substitutions' => 0,
            'insertions' => 0,
            'deletions' => 0,
            'character_edits' => 0,
        ];

        foreach ($clips as $clip) {
            foreach (array_keys($summary) as $field) {
                if ($field !== 'clips') {
                    $summary[$field] += $clip['metrics'][$field];
                }
            }
        }

        $wordErrors = $summary['substitutions'] + $summary['insertions'] + $summary['deletions'];
        $summary['wer'] = $summary['reference_words'] === 0 ? 0.0 : $wordErrors / $summary['reference_words'];
        $summary['cer'] = $summary['reference_characters'] === 0 ? 0.0 : $summary['character_edits'] / $summary['reference_characters'];

        return $summary;
    }

    private function outputPath(string $manifestPath): string
    {
        $configuredOutput = $this->option('output');

        if (is_string($configuredOutput) && $configuredOutput !== '') {
            return $configuredOutput;
        }

        $extension = pathinfo($manifestPath, PATHINFO_EXTENSION);
        $basePath = $extension === '' ? $manifestPath : substr($manifestPath, 0, -strlen($extension) - 1);

        return $basePath.'.results.json';
    }
}
