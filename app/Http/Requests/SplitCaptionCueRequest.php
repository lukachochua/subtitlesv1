<?php

namespace App\Http\Requests;

use App\Models\CaptionCue;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class SplitCaptionCueRequest extends FormRequest
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
            'split_ms' => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * @return array<callable(Validator): void>
     */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->has('split_ms')) {
                return;
            }

            $captionCue = $this->route('captionCue');

            if (! $captionCue instanceof CaptionCue) {
                return;
            }

            $splitMs = $this->integer('split_ms');

            if ($splitMs <= $captionCue->start_ms || $splitMs >= $captionCue->end_ms) {
                $validator->errors()->add(
                    'split_ms',
                    'The split time must be inside the cue interval.',
                );
            }

            $words = preg_split('/\s+/u', trim($captionCue->text)) ?: [];

            if (count($words) < 2) {
                $validator->errors()->add(
                    'split_ms',
                    'The cue must contain at least two words before it can be split.',
                );
            }
        }];
    }
}
