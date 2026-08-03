<?php

namespace App\Models;

use Database\Factories\SeasonFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'entry_fee', 'winner_user_id', 'payout_at', 'payout_marked_by_id'])]
class Season extends Model
{
    /** @use HasFactory<SeasonFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'entry_fee' => 'decimal:2',
            'payout_at' => 'datetime',
        ];
    }

    public function rounds(): HasMany
    {
        return $this->hasMany(Round::class);
    }

    public function potPayments(): HasMany
    {
        return $this->hasMany(PotPayment::class);
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winner_user_id');
    }

    public function payoutMarkedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payout_marked_by_id');
    }
}
