<?php

namespace App\Http\Requests;

use App\Models\CaptionCue;
use App\Models\VideoProject;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateCaptionCueStartTimeRequest extends FormRequest
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
            'start_ms' => ['required', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<callable(Validator): void>
     */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->has('start_ms')) {
                return;
            }

            $captionCue = $this->route('captionCue');
            $videoProject = $this->route('videoProject');

            if (! $captionCue instanceof CaptionCue || ! $videoProject instanceof VideoProject) {
                return;
            }

            $startMs = $this->integer('start_ms');

            if ($startMs >= $captionCue->end_ms) {
                $validator->errors()->add(
                    'start_ms',
                    'The cue start must be before its end.',
                );
            }

            if ($videoProject->duration_ms === null) {
                $validator->errors()->add(
                    'start_ms',
                    'The video must have a known duration before cue timing can be edited.',
                );

                return;
            }

            if ($startMs > $videoProject->duration_ms) {
                $validator->errors()->add(
                    'start_ms',
                    'The cue start must not exceed the video duration.',
                );
            }
        }];
    }
}
