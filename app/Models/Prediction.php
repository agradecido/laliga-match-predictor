<?php

namespace App\Models;

use Database\Factories\PredictionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'fixture_id', 'choice'])]
class Prediction extends Model
{
    /** @use HasFactory<PredictionFactory> */
    use HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fixture(): BelongsTo
    {
        return $this->belongsTo(Fixture::class);
    }

    public function isCorrect(): bool
    {
        return $this->choice === $this->fixture->resultSign();
    }
}
