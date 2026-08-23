<?php

namespace App\Actions;

use App\Enums\TranscriptionStatus;
use App\Models\CaptionCue;
use App\Models\VideoProject;
use Illuminate\Database\Eloquent\Collection;
use LogicException;
use RuntimeException;
use Throwable;

class GenerateVideoProjectCaptions
{
    public const FAILURE_MESSAGE = 'Captions could not be generated. Check the local NeMo setup and source audio, then try again.';

    public function __construct(
        private InspectVideoProject $inspectVideoProject,
        private ExtractVideoProjectAudio $extractVideoProjectAudio,
        private TranscribeVideoProjectAudioWithNemo $transcribeVideoProjectAudioWithNemo,
        private LoadVideoProjectCaptionData $loadVideoProjectCaptionData,
        private PersistGeneratedCaptionCues $persistGeneratedCaptionCues,
    ) {}

    /**
     * @return Collection<int, CaptionCue>
     */
    public function handle(VideoProject $videoProject): Collection
    {
        if ($videoProject->captionCues()->exists()) {
            throw new LogicException('Saved captions already exist and will not be overwritten.');
        }

        $videoProject->update([
            'transcription_status' => TranscriptionStatus::Pending,
            'transcription_error' => null,
        ]);
        $videoProject->update([
            'transcription_status' => TranscriptionStatus::Processing,
        ]);

        try {
            if ($videoProject->duration_ms === null) {
                $videoProject = $this->inspectVideoProject->handle($videoProject);
            }

            $audioPath = $this->extractVideoProjectAudio->handle($videoProject);
            $this->transcribeVideoProjectAudioWithNemo->handle($videoProject, $audioPath);
            $captionData = $this->loadVideoProjectCaptionData->handle($videoProject);

            if ($captionData['cues'] === []) {
                throw new RuntimeException('The transcription did not produce any caption cues.');
            }

            $savedCues = $this->persistGeneratedCaptionCues->handle($videoProject, $captionData['cues']);

            $videoProject->update([
                'transcription_status' => TranscriptionStatus::Completed,
                'transcription_error' => null,
                'transcribed_at' => now(),
            ]);

            return $savedCues;
        } catch (Throwable $exception) {
            $videoProject->update([
                'transcription_status' => TranscriptionStatus::Failed,
                'transcription_error' => self::FAILURE_MESSAGE,
            ]);

            throw $exception;
        }
    }
}
