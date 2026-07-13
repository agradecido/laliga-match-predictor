# LaLiga Quiniela

A soccer prediction platform ("Quiniela") for the Spanish Football League (LaLiga). Users submit 1/X/2 predictions for each matchday's fixtures before kickoff, and scores are calculated automatically once official results are entered.

## Tech Stack

- **Backend:** PHP 8.3+, Laravel
- **Frontend:** Vue 3 (Composition API, `<script setup>`) + TypeScript
- **SPA Connector:** Inertia.js
- **Styling:** Tailwind CSS
- **Database:** SQLite (development)

## Getting Started

```bash
composer install
npm install

cp .env.example .env
php artisan key:generate

touch database/database.sqlite
php artisan migrate --seed

npm run build   # or `npm run dev` for local development
php artisan serve
```

## Development

```bash
npm run dev          # Vite dev server with HMR
php artisan test     # Run the test suite
```
