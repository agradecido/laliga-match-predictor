<?php

namespace App\Http\Controllers\Quiniela;

use App\Http\Controllers\Controller;
use App\Http\Requests\Quiniela\StorePredictionsRequest;
use App\Models\Prediction;
use App\Models\Round;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class PredictionController extends Controller
{
    public function store(StorePredictionsRequest $request, Round $round): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($request, $data) {
            foreach ($data['predictions'] as $pick) {
                Prediction::updateOrCreate(
                    [
                        'user_id' => $request->user()->id,
                        'fixture_id' => $pick['fixture_id'],
                    ],
                    ['choice' => $pick['choice']],
                );
            }
        });

        return back();
    }
}
