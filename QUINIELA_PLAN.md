# Quiniela feature — implementation plan

## Context

The user wants to build the app's core feature: a "quiniela" (Spanish football score-prediction pool). Users pick 1/X/2 per match for each LaLiga jornada before a deadline; an admin enters real results afterward; points accumulate into a season-long leaderboard. This is greenfield — the repo currently only has Laravel's stock Breeze auth scaffold (User model, Profile controller, no domain tables).

Two data assets are already in the repo, prepared by the user:
- `resources/json/schedule-laliga-2026-2027.json` — the 2026/2027 calendar (38 matchdays × 10 fixtures, `season`/`matchdays`/`matchday`/`date`/`matches`/`home_team`/`away_team`/`home_team_normalized`/`away_team_normalized` schema).
- `public/assets/images/team-logos/*.svg` — 20 team crest SVGs, filename = `normalized_name` (e.g. `deportivo-alaves.svg`), served directly as static assets at `/assets/images/team-logos/{slug}.svg` (no Vite pipeline needed — plain `<img>` src).

Confirmed product decisions (from prior discussion with the user):
- Scoring: 1X2 only, 1 point per correct pick, summed across the season for the leaderboard.
- Results entered manually by an admin (`users.is_admin` boolean).
- A round (jornada) locks for predictions at **08:00 on its `date` field** (`locksAt = date + 08:00`, `isLocked = now() >= locksAt`) — fully automatic, no manual open/close toggle.
- Development must follow TDD — write the failing Pest test first, then the minimum code to pass it, for every slice below. This is also captured as a standing convention in `AGENTS.md` (alongside the English-only and semantic-commit rules).

Known data quirks (surfaced earlier, not to be "fixed" silently): the JSON's 38 matchdays reference 23 distinct normalized teams, not 20 — jornadas 1–3 use `racing-santander`/`malaga-cf`/`deportivo-la-coruna`, jornadas 4–38 use `girona-fc`/`real-oviedo`/`rcd-mallorca`. Also 3 team-normalized names have no matching SVG file (`girona-fc`, `rcd-mallorca`, `villarreal-cf`) — the crest `<img>` needs a graceful fallback (e.g. `@error` hides the broken image / shows team initials) for those.

## Domain model

- `Team` (`teams`): `name`, `normalized_name` (unique) — keyed by the JSON's stable slug, which also matches the logo filenames.
- `Season` (`seasons`): `name` (unique, e.g. "2026/2027").
- `Round` (`rounds`): `season_id` FK, `number` (int, parsed from "Jornada N"), `match_date` (date). Unique on `(season_id, number)`. Methods: `locksAt(): Carbon`, `isLocked(): bool`.
- `Fixture` (`fixtures`) — named `Fixture`, not `Match`, because `Match` collides with PHP 8's `match` keyword as a class name: `round_id` FK, `home_team_id`/`away_team_id` FK teams, `home_score`/`away_score` nullable int. Method `resultSign(): ?string` ('1'/'X'/'2', null if unplayed).
- `Prediction` (`predictions`): `user_id` FK, `fixture_id` FK, `choice` ('1'|'X'|'2'). Unique on `(user_id, fixture_id)`.
- `users.is_admin` boolean, default false, NOT mass-assignable (only ever set via seeder/tinker). Add `'is_admin' => 'boolean'` to `User::casts()`.

Points/leaderboard are computed on the fly (no stored points column) — compare `prediction.choice` to `fixture.resultSign()` in PHP via collections; dataset is tiny (≤20 users × 380 fixtures), no caching needed.

## TDD build order

Each slice: write the Pest test(s) first (confirm they fail for the right reason), then implement, then confirm green, before moving to the next slice.

1. **Migrations + models** — `tests/Feature/Quiniela/RoundLockingTest.php` first (using model factories with explicit past/future `match_date`, since the seed data's actual dates shouldn't be relied on for exercising both branches): asserts `Round::isLocked()` true/false around the 08:00 boundary. Then create migrations (`seasons`, `teams` with `normalized_name` unique, `rounds` unique `(season_id, number)`, `fixtures` unique `(round_id, home_team_id, away_team_id)`, `predictions` unique `(user_id, fixture_id)` with `choice` as a SQLite-backed `enum` (`Schema::enum`, compiles to a CHECK constraint), `add_is_admin_to_users_table`) and the models (`Round::locksAt()`/`isLocked()`, `Fixture::resultSign()`), plus factories (`TeamFactory`, `SeasonFactory`, `RoundFactory` with `->locked()`/`->open()` states, `FixtureFactory` with a `->played(home, away)` state, `PredictionFactory`).

2. **Import command** — `tests/Feature/Quiniela/ImportCommandTest.php` first: importing `resources/json/schedule-laliga-2026-2027.json` creates 1 season, 38 rounds, 23 teams, 380 fixtures; re-running twice doesn't duplicate rows (idempotency); re-running after a score was entered on a fixture does not clear it. Then implement `app/Console/Commands/ImportQuinielaCalendar.php` (`quiniela:import {path=resources/json/schedule-laliga-2026-2027.json}`): `Season`/`Round`/`Team` via `updateOrCreate`, `Fixture` via `firstOrCreate` (never touches scores on re-import — this is deliberate and test-covered).

3. **Predictions** — `tests/Feature/Quiniela/PredictionSubmissionTest.php` first: authenticated user can submit for an open round (redirect + rows persisted); resubmitting updates instead of duplicating; submitting to a locked round → 403, nothing written; submitting a `fixture_id` belonging to a *different* round → rejected (guards against smuggling a prediction onto a still-open round via another round's URL); invalid `choice` → 422; guest → redirected to login. Then implement `EnsureUserIsAdmin` middleware + `admin` alias in `bootstrap/app.php`, `routes/quiniela.php` (required from `routes/web.php` like `auth.php`), `app/Http/Controllers/Quiniela/RoundController` (`index`/`show`) and `PredictionController` (`store`: lock check before validation, fixture-belongs-to-round check, `updateOrCreate` per pick inside a transaction).

4. **Admin results** — `tests/Feature/Quiniela/AdminResultsTest.php` first: non-admin → 403 on all three admin routes; admin can view and submit scores; cross-round fixture guard same as predictions; no lock check on this controller (scores are entered after a round is locked/played, so gating on `isLocked()` would be backwards). Then implement `Admin\ResultsController` (`index`/`edit`/`update`).

5. **Leaderboard** — `tests/Feature/Quiniela/LeaderboardTest.php` first: seed multiple users with a mix of correct/incorrect/pending predictions across several fixtures, assert exact point totals and ranking order, and that an unplayed fixture contributes 0 points regardless of choice (not a false match against a null `resultSign()`). Then implement `LeaderboardController::index`.

6. **Frontend** (no Pest coverage — manual verification per the Verification section): `Pages/Quiniela/Index.vue`, `Show.vue`, `Leaderboard.vue`, `Pages/Admin/ResultsEdit.vue`, nav links in both the desktop and mobile blocks of `Layouts/AuthenticatedLayout.vue` (admin link gated on `$page.props.auth.user.is_admin`), new `resources/js/types/quiniela.d.ts`. Team crest: plain `<img :src="\`/assets/images/team-logos/${team.normalized_name}.svg\`" @error="...">` with a fallback (hide image / show initials) for the 3 teams with no SVG — no Vite/`import.meta.glob` needed since these are static files under `public/`.

## Supporting changes

- `database/seeders/DatabaseSeeder.php`: flag the existing "Test User" as `is_admin => true` (dev convenience), call `Artisan::call('quiniela:import')` (reuses the tested importer, avoids a parallel seeding path).
- `AGENTS.md`: Testing section added — write a failing test before implementation code for every new feature (TDD); run the suite before considering a slice done.
- `README.md`: fixed the stale `resources/images/team-logos` reference to `public/assets/images/team-logos`.

## Verification

```bash
docker compose -f .devcontainer/docker-compose.yml exec app php artisan test
docker compose -f .devcontainer/docker-compose.yml exec app php artisan migrate
docker compose -f .devcontainer/docker-compose.yml exec app php artisan quiniela:import
docker compose -f .devcontainer/docker-compose.yml exec app php artisan db:seed
```
Then in the browser: log in as `test@example.com` (now admin), visit `/quiniela` (list of 38 rounds with open/locked status), open one, submit picks, resubmit to confirm upsert; visit `/admin/results`, enter scores for a round; visit `/quiniela-leaderboard` and confirm points reflect the entered results. Since the current seed dates are already in the future relative to today, the locked-state UI path won't show up naturally — that's exactly what `RoundFactory`'s `->locked()` state and the Pest boundary test cover instead of relying on real seed data.
