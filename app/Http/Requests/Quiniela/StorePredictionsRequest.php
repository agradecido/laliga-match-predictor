<?php

namespace App\Http\Requests\Quiniela;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePredictionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return ! $this->route('round')->isLocked();
    }

    public function rules(): array
    {
        $round = $this->route('round');

        return [
            'predictions' => ['required', 'array'],
            'predictions.*.fixture_id' => [
                'required',
                'integer',
                Rule::exists('fixtures', 'id')->where('round_id', $round->id),
            ],
            'predictions.*.choice' => ['required', Rule::in(['1', 'X', '2'])],
        ];
    }
}
