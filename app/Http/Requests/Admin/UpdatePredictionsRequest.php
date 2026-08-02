<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePredictionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $round = $this->route('round');

        return [
            'entries' => ['required', 'array'],
            'entries.*.user_id' => ['required', 'integer', 'exists:users,id'],
            'entries.*.fixture_id' => [
                'required',
                'integer',
                Rule::exists('fixtures', 'id')->where('round_id', $round->id),
            ],
            'entries.*.choice' => ['nullable', Rule::in(['1', 'X', '2'])],
        ];
    }
}
