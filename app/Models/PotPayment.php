<?php

namespace App\Models;

use Database\Factories\PotPaymentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['season_id', 'user_id', 'paid_at', 'marked_by_id'])]
class PotPayment extends Model
{
    /** @use HasFactory<PotPaymentFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
        ];
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function markedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by_id');
    }
}
