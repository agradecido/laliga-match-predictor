<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoundRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'season_id' => ['required', 'integer', 'exists:seasons,id'],
            'number' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('rounds')->where('season_id', $this->input('season_id')),
            ],
            'match_date' => ['required', 'date'],
        ];
    }
}
