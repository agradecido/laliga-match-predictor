<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Prediction;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        $users = User::query()
            ->orderBy('name')
            ->get()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_admin' => $user->is_admin,
                'created_at' => $user->created_at->toDateString(),
            ]);

        return Inertia::render('Admin/UsersIndex', [
            'users' => $users,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/UsersCreate');
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'is_admin' => $data['is_admin'] ?? false,
            'email_verified_at' => now(),
        ]);

        return redirect()->route('admin.users.index');
    }

    public function show(User $user): Response
    {
        $user->load('predictions.fixture.homeTeam', 'predictions.fixture.awayTeam', 'predictions.fixture.round');

        $rounds = $user->predictions
            ->groupBy(fn (Prediction $prediction) => $prediction->fixture->round_id)
            ->map(function ($predictions) {
                $round = $predictions->first()->fixture->round;

                return [
                    'round_number' => $round->number,
                    'match_date' => $round->match_date->toDateString(),
                    'predictions' => $predictions->map(fn (Prediction $prediction) => [
                        'id' => $prediction->id,
                        'home_team' => $prediction->fixture->homeTeam->only('name', 'normalized_name'),
                        'away_team' => $prediction->fixture->awayTeam->only('name', 'normalized_name'),
                        'choice' => $prediction->choice,
                        'result_sign' => $prediction->fixture->resultSign(),
                        'is_correct' => $prediction->fixture->resultSign() !== null ? $prediction->isCorrect() : null,
                    ])->values(),
                ];
            })
            ->sortBy('round_number')
            ->values();

        return Inertia::render('Admin/UsersShow', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_admin' => $user->is_admin,
                'created_at' => $user->created_at->toDateString(),
            ],
            'rounds' => $rounds,
        ]);
    }

    public function edit(User $user): Response
    {
        return Inertia::render('Admin/UsersEdit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_admin' => $user->is_admin,
            ],
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        if ($user->is($request->user())) {
            $data['is_admin'] = true;
        }

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('admin.users.index');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_if($user->is($request->user()), 403);

        $user->delete();

        return redirect()->route('admin.users.index');
    }
}
