<?php

namespace App\Http\Requests;

use App\Models\CaptionCue;
use App\Models\VideoProject;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class MergeCaptionCueWithNextRequest extends FormRequest
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
        return [];
    }

    /**
     * @return array<callable(Validator): void>
     */
    public function after(): array
    {
        return [function (Validator $validator): void {
            $captionCue = $this->route('captionCue');
            $videoProject = $this->route('videoProject');

            if (! $captionCue instanceof CaptionCue || ! $videoProject instanceof VideoProject) {
                return;
            }

            $hasNextCue = $videoProject->captionCues()
                ->where('order', '>', $captionCue->order)
                ->exists();

            if (! $hasNextCue) {
                $validator->errors()->add(
                    'caption_cue',
                    'The last caption cue cannot be merged with a next cue.',
                );
            }
        }];
    }
}
