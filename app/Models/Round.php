<?php

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\RoundFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['season_id', 'number', 'match_date'])]
class Round extends Model
{
    /** @use HasFactory<RoundFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'match_date' => 'date',
        ];
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function fixtures(): HasMany
    {
        return $this->hasMany(Fixture::class);
    }

    public function locksAt(): Carbon
    {
        return $this->match_date->copy()->setTime(8, 0);
    }

    public function isLocked(): bool
    {
        return Carbon::now()->greaterThanOrEqualTo($this->locksAt());
    }

    public function closesAt(): ?Carbon
    {
        $lastKickoff = $this->fixtures->max('kickoff_at');

        return $lastKickoff?->copy()->addDay();
    }

    public static function current(): ?self
    {
        $rounds = static::query()->with('fixtures')->orderBy('number')->get();

        return $rounds->first(fn (self $round) => $round->closesAt() && Carbon::now()->lessThanOrEqualTo($round->closesAt()))
            ?? $rounds->last();
    }
}
