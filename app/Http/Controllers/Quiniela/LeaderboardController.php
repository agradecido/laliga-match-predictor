<?php

namespace App\Http\Controllers\Quiniela;

use App\Http\Controllers\Controller;
use App\Models\Round;
use App\Models\User;
use App\Support\Leaderboard;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LeaderboardController extends Controller
{
    public function index(Request $request): Response
    {
        $leaderboard = Leaderboard::compute()->map(fn (array $entry) => [
            'name' => $entry['name'],
            'points' => $entry['points'],
        ]);

        return Inertia::render('Quiniela/Leaderboard', [
            'leaderboard' => $leaderboard,
            'pot' => $this->potPayload($request),
        ]);
    }

    private function potPayload(Request $request): ?array
    {
        $season = Round::current()?->season;

        if (! $season || $season->entry_fee === null) {
            return null;
        }

        $paidCount = $season->potPayments()->whereNotNull('paid_at')->count();
        $playersCount = User::query()->count();

        $hasPaid = $season->potPayments()
            ->where('user_id', $request->user()->id)
            ->whereNotNull('paid_at')
            ->exists();

        return [
            'entry_fee' => (float) $season->entry_fee,
            'total_collected' => $paidCount * (float) $season->entry_fee,
            'total_expected' => $playersCount * (float) $season->entry_fee,
            'has_paid' => $hasPaid,
            'winner_name' => $season->winner?->name,
            'payout_at' => $season->payout_at?->toDateString(),
        ];
    }
}
