<?php

namespace App\Http\Requests;

use App\Models\CaptionCue;
use App\Models\VideoProject;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateCaptionCueEndTimeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'end_ms' => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * @return array<callable(Validator): void>
     */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->has('end_ms')) {
                return;
            }

            $captionCue = $this->route('captionCue');
            $videoProject = $this->route('videoProject');

            if (! $captionCue instanceof CaptionCue || ! $videoProject instanceof VideoProject) {
                return;
            }

            $endMs = $this->integer('end_ms');

            if ($endMs <= $captionCue->start_ms) {
                $validator->errors()->add('end_ms', 'The cue end must be after its start.');
            }

            if ($videoProject->duration_ms === null) {
                $validator->errors()->add(
                    'end_ms',
                    'The video must have a known duration before cue timing can be edited.',
                );

                return;
            }

            if ($endMs > $videoProject->duration_ms) {
                $validator->errors()->add(
                    'end_ms',
                    'The cue end must not exceed the video duration.',
                );
            }
        }];
    }
}
