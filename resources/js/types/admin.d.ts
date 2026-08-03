import type { PredictionChoice, Team } from '@/types/quiniela';

export interface Season {
    id: number;
    name: string;
}

export interface AdminRound {
    id: number;
    season_id: number;
    season_name: string;
    number: number;
    match_date: string;
    is_locked: boolean;
}

export interface AdminUser {
    id: number;
    name: string;
    email: string;
    is_admin: boolean;
    created_at: string;
}

export interface AdminUserPrediction {
    id: number;
    home_team: Team;
    away_team: Team;
    choice: PredictionChoice;
    result_sign: PredictionChoice | null;
    is_correct: boolean | null;
}

export interface AdminUserRound {
    round_number: number;
    match_date: string;
    predictions: AdminUserPrediction[];
}

export interface AdminPredictionFixture {
    id: number;
    home_team: Team;
    away_team: Team;
    result_sign: PredictionChoice | null;
}

export interface AdminPlayerPick {
    fixture_id: number;
    choice: PredictionChoice | null;
}

export interface AdminPlayerPicks {
    id: number;
    name: string;
    picks: AdminPlayerPick[];
}

export interface AdminPotSeason {
    id: number;
    name: string;
    entry_fee: number | null;
    winner_name: string | null;
    payout_at: string | null;
}

export interface AdminPotPlayer {
    id: number;
    name: string;
    has_paid: boolean;
}
