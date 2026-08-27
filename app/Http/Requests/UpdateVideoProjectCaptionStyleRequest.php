<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVideoProjectCaptionStyleRequest extends FormRequest
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
            'font' => ['required', Rule::in(['georgian_sans', 'system_sans', 'georgian_serif'])],
            'font_size_px' => ['required', 'integer', 'between:12,72'],
            'bold' => ['required', 'boolean'],
            'italic' => ['required', 'boolean'],
            'text_color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'background_color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'background_opacity_percent' => ['required', 'integer', 'between:0,100'],
            'text_alignment' => ['required', Rule::in(['left', 'center', 'right'])],
            'vertical_position_percent' => ['required', 'integer', 'between:0,100'],
            'outline_color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'outline_width_px' => ['required', 'numeric', 'between:0,4', 'multiple_of:0.5'],
            'shadow' => ['required', 'boolean'],
            'active_word_enabled' => ['required', 'boolean'],
            'active_word_color' => ['required', 'hex_color'],
            'active_word_style' => ['required', Rule::in(['text', 'background'])],
        ];
    }
}
