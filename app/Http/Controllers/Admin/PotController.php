<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateEntryFeeRequest;
use App\Http\Requests\Admin\UpdatePotPayoutRequest;
use App\Models\PotPayment;
use App\Models\Round;
use App\Models\User;
use App\Support\Leaderboard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PotController extends Controller
{
    public function index(): Response
    {
        $season = Round::current()?->season;

        $users = User::query()->orderBy('name')->get();

        $paidAt = $season ? $season->potPayments()->whereNotNull('paid_at')->pluck('user_id') : collect();

        $players = $users->map(fn (User $user) => [
            'id' => $user->id,
            'name' => $user->name,
            'has_paid' => $paidAt->contains($user->id),
        ]);

        $entryFee = $season?->entry_fee !== null ? (float) $season->entry_fee : null;
        $leader = $season ? Leaderboard::compute($season)->first() : null;

        return Inertia::render('Admin/PotIndex', [
            'season' => $season ? [
                'id' => $season->id,
                'name' => $season->name,
                'entry_fee' => $entryFee,
                'winner_name' => $season->winner?->name,
                'payout_at' => $season->payout_at?->toDateString(),
            ] : null,
            'players' => $players,
            'total_collected' => $entryFee !== null ? $paidAt->count() * $entryFee : 0,
            'total_expected' => $entryFee !== null ? $users->count() * $entryFee : 0,
            'leader_name' => $leader['name'] ?? null,
        ]);
    }

    public function updateFee(UpdateEntryFeeRequest $request): RedirectResponse
    {
        $season = Round::current()?->season;

        abort_unless($season, 404);

        $season->update(['entry_fee' => $request->validated('entry_fee')]);

        return back();
    }

    public function togglePayment(Request $request, User $user): RedirectResponse
    {
        $season = Round::current()?->season;

        abort_unless($season, 404);

        $payment = PotPayment::firstOrNew([
            'season_id' => $season->id,
            'user_id' => $user->id,
        ]);

        $payment->paid_at = $payment->paid_at ? null : now();
        $payment->marked_by_id = $request->user()->id;
        $payment->save();

        return back();
    }

    public function payout(UpdatePotPayoutRequest $request): RedirectResponse
    {
        $season = Round::current()?->season;

        abort_unless($season, 404);

        $season->update([
            'winner_user_id' => $request->validated('winner_user_id'),
            'payout_at' => now(),
            'payout_marked_by_id' => $request->user()->id,
        ]);

        return back();
    }
}
