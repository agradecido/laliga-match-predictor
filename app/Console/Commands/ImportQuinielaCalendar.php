<?php

namespace App\Console\Commands;

use App\Models\Fixture;
use App\Models\Round;
use App\Models\Season;
use App\Models\Team;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ImportQuinielaCalendar extends Command
{
    protected $signature = 'quiniela:import {path=resources/json/schedule-laliga-2026-2027.json}';

    protected $description = 'Import the LaLiga calendar (seasons, rounds, teams and fixtures) from a schedule JSON file';

    public function handle(): int
    {
        $path = $this->argument('path');

        if (! File::exists($path)) {
            $this->error("File not found: {$path}");

            return self::FAILURE;
        }

        $data = json_decode(File::get($path), true);

        $season = Season::updateOrCreate(['name' => $data['season']]);

        foreach ($data['matchdays'] as $matchday) {
            $number = (int) preg_replace('/\D+/', '', $matchday['matchday']);

            $round = Round::updateOrCreate(
                ['season_id' => $season->id, 'number' => $number],
                ['match_date' => $matchday['date']],
            );

            foreach ($matchday['matches'] as $match) {
                $homeTeam = Team::updateOrCreate(
                    ['normalized_name' => $match['home_team_normalized']],
                    ['name' => $match['home_team']],
                );

                $awayTeam = Team::updateOrCreate(
                    ['normalized_name' => $match['away_team_normalized']],
                    ['name' => $match['away_team']],
                );

                Fixture::firstOrCreate([
                    'round_id' => $round->id,
                    'home_team_id' => $homeTeam->id,
                    'away_team_id' => $awayTeam->id,
                ]);
            }
        }

        $this->info('Quiniela calendar imported successfully.');

        return self::SUCCESS;
    }
}
