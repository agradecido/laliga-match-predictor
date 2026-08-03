export interface Team {
    name: string;
    normalized_name: string;
}

export interface RoundSummary {
    id: number;
    number: number;
    match_date: string;
    is_locked: boolean;
}

export type PredictionChoice = '1' | 'X' | '2';

export interface QuinielaFixture {
    id: number;
    home_team: Team;
    away_team: Team;
    kickoff_at: string | null;
    choice: PredictionChoice | null;
}

export interface AdminFixture {
    id: number;
    home_team: Team;
    away_team: Team;
    home_score: number | null;
    away_score: number | null;
}

export interface LeaderboardEntry {
    name: string;
    points: number;
}

export interface PotSummary {
    entry_fee: number;
    total_collected: number;
    total_expected: number;
    has_paid: boolean;
    winner_name: string | null;
    payout_at: string | null;
}
