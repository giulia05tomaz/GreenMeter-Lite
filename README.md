# GreenMeter Lite

GreenMeter Lite is a minimal MVP to track and visualise energy consumption and associated carbon emissions. It consists of a Laravel 11 backend (PHP 8.3) and a React frontend (Vite + TypeScript).

## Backend

- **Laravel 11** using Sanctum for authentication.
- **Models**:
  - `Reading` for time‑stamped consumption values.
  - `EmissionFactor` defines conversion factors (e.g. `0.000053 tCO₂e/kWh`).
- **Endpoints**:
  - **`POST /api/auth/login`** – Authenticates a user and returns a Sanctum token.
  - **`POST /api/readings/upload`** – Accepts a CSV file (`timestamp`, `metric`, `value`, `unit`) and bulk‑inserts rows.
  - **`GET /api/dashboard/kpis?from=YYYY‑MM‑DD&to=YYYY‑MM‑DD`** – Returns aggregate totals for energy (kWh), CO₂e (t) and daily average.
  - **`GET /api/dashboard/series?from=&to=`** – Returns a daily series (date, kWh, CO₂e).
  - **`GET /api/alerts?from=&to=`** – Returns days where the daily average energy exceeds 130 % of the weekly average.

- **Database migrations** define tables for users, readings and emission factors. A seed inserts the energy emission factor and an admin user (`admin@demo.com` / `admin123`).
- **Tests** written with [Pest](https://pestphp.com) cover authentication and CSV uploads.

To set up locally:

```bash
cd backend
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

This will create the necessary tables and seed the emission factor and admin user. Use the supplied credentials to obtain an API token.

### Docker (optional)

A `docker-compose.yml` is provided. It builds a `php-fpm` service for the backend and a `node` service for the frontend. To run:

```bash
docker compose up --build
```

The Laravel app will be available at `http://localhost:8000`, and the React app at `http://localhost:5173`.

## Frontend

The React frontend uses Vite and TypeScript. React Query handles API requests and state, and Tailwind CSS provides a minimal layout. There are three screens:

- **Login** – Prompts for email and password and stores the Sanctum token in local storage.
- **Upload** – Lets an authenticated user upload a CSV file. It shows success or error messages.
- **Dashboard** – Shows three cards with totals, a daily line chart (via [Chart.js](https://www.chartjs.org/)) and a table of peak‑day alerts.

To run locally:

```bash
cd frontend
npm install
npm run dev
```

## Sample data

The `/samples` directory contains an example CSV file (`energy_readings.csv`) with columns `timestamp`, `metric`, `value` and `unit`.

## Design considerations

- **CSV ingestion** – The MVP assumes CSV is the simplest way for users to bring historical readings into the system. In a real‑world application this would be replaced or augmented with automated data sources.
- **Peak alert logic** – A “peak day” is flagged when that day’s energy consumption is greater than 130 % of the weekly average consumption. This simple heuristic highlights unusual spikes without complex analytics.