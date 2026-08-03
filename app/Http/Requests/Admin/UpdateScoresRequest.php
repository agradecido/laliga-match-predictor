<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateScoresRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $round = $this->route('round');

        return [
            'scores' => ['required', 'array'],
            'scores.*.fixture_id' => [
                'required',
                'integer',
                Rule::exists('fixtures', 'id')->where('round_id', $round->id),
            ],
            'scores.*.home_score' => ['nullable', 'integer', 'min:0'],
            'scores.*.away_score' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
