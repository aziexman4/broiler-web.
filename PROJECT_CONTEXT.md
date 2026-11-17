# PROJECT_CONTEXT.md — BroilerApp Pro (SmartCoop)

> **AI Agent Memory & Technical Context File**
> Last Audited: 2026-11-14 | Workspace: `c:\laragon\www\broiler-web`

---

## 1. System Overview & Stack

- **Framework**: **Laravel 13.8** (PHP `^8.3`, Laravel Breeze `^2.4` auth scaffolding). *Note: This is a Laravel MVC application, NOT CodeIgniter 4.*
- **Frontend Stack**: Blade Templates, Tailwind CSS (`^3.1.0` + `@tailwindcss/vite` `^4.0.0`), Alpine.js (`^3.4.2`), Chart.js (CDN), Vite (`^8.0.0`).
- **PDF & Reporting**: `barryvdh/laravel-dompdf` (`^3.1`), HTML Print Layout (`layouts/print.blade.php`), Streamed CSV (UTF-8 BOM).
- **Environment Config (`.env`)**:
  - `APP_ENV=local`, `APP_DEBUG=true`, `APP_URL=http://localhost`, `APP_LOCALE=en`
  - `DB_CONNECTION=sqlite` (`database/database.sqlite` active)
  - `SESSION_DRIVER=database`, `SESSION_LIFETIME=120`, `QUEUE_CONNECTION=database`, `CACHE_STORE=database`
  - `BCRYPT_ROUNDS=12`, `MAIL_MAILER=log`
- **Authentication & Authorization**:
  - **Session Guard**: Standard Laravel Breeze session auth (`auth`, `verified`, `guest` middleware).
  - **Roles**: Lowercase strings `'owner'` and `'worker'` stored in `users.role` (`User::isOwner()`, `User::isWorker()`).
  - **Enterprise Gate**: `Gate::define('manage-enterprise', fn(User $user) => $user->role === 'owner')` in `app/Providers/AppServiceProvider.php`.
  - **Flock Policy**: `app/Policies/FlockPolicy.php` — `create`/`delete` require `owner`; `view`/`update`/`manageDailyRecords` allow `owner` OR assigned worker (`$user->id === $flock->worker_id`).
  - **Audit Trail**: `app/Traits/LogsActivity.php` attached to `Flock`, `DailyRecord`, and `FeedInventory` (logs `created`, `updated` with `before`/`after` JSON diff, `deleted` to `activity_logs` when `Auth::check()` is true).

---

## 2. Database Schema & Relations

Active SQLite database (`database/database.sqlite`, 21 tables including framework tables `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `migrations`, `password_reset_tokens`, `sessions`).

### 2.1. Domain Tables

| Table | Primary Key | Columns (Exact Name & Type) | Foreign Keys & Relations |
| :--- | :--- | :--- | :--- |
| **`users`** | `id` | `name` (string), `email` (string, unique), `role` (string, default `'worker'`), `base_salary` (integer, default `2500000`), `email_verified_at` (timestamp, nullable), `password` (string), `remember_token` | HasMany: `flocks` (`worker_id`), `payrolls` (`user_id`), `activityLogs` (`user_id`) |
| **`coops`** | `id` | `name` (string), `tagline` (string, nullable), `address` (text), `phone` (string, nullable), `email` (string, nullable), `manager_name` (string), `is_headquarters` (boolean, default `false`) | HasMany: `flocks` (`coop_id`) |
| **`flocks`** | `id` | `coop_id` (nullable), `name` (string), `breed` (string), `start_date` (date), `initial_population` (uint), `status` (enum `'active'`, `'closed'`, default `'active'`), `stock_pre_starter_kg` (decimal 10,2, default `0`), `stock_starter_kg` (decimal 10,2, default `0`), `stock_finisher_kg` (decimal 10,2, default `0`), `worker_id` (nullable), `closed_at` (timestamp, nullable) | BelongsTo: `coop` $\rightarrow$ `coops.id` (`nullOnDelete`), `worker` $\rightarrow$ `users.id` (`nullOnDelete`). HasMany: `dailyRecords`, `feedRequests`, `growthSamplings`, `harvests` |
| **`daily_records`** | `id` | `flock_id`, `record_date` (date), `mortality` (uint, default `0`), `culling` (uint, default `0`), `feed_pre_starter_kg` (decimal 8,2, default `0.00`), `feed_starter_kg` (decimal 8,2, default `0.00`), `feed_finisher_kg` (decimal 8,2, default `0.00`), `average_weight_gram` (integer, nullable) | BelongsTo: `flock` $\rightarrow$ `flocks.id` (`cascadeOnDelete`). *Virtual Accessor*: `feed_consumed_kg` |
| **`harvests`** | `id` | `flock_id`, `harvest_date` (date), `total_chickens_sold` (uint), `total_weight_kg` (decimal 10,2), `buyer_name` (string), `chicken_price_per_kg` (decimal 12,2, default `0`), `feed_price_per_kg` (decimal 12,2, default `0`), `total_feed_cost` (decimal 15,2, default `0`), `gross_revenue` (decimal 15,2, default `0`), `net_profit` (decimal 15,2, default `0`), `revenue` (decimal 15,2, default `0`) | BelongsTo: `flock` $\rightarrow$ `flocks.id` (`cascadeOnDelete`) |
| **`feed_inventories`** | `id` | `transaction_date` (date), `feed_name` (string), `transaction_type` (enum `'in'`, `'out'`), `quantity_kg` (decimal 10,2), `price_per_kg` (integer, nullable), `notes` (string, nullable), `flock_id` (nullable) | BelongsTo: `flock` $\rightarrow$ `flocks.id` (`nullOnDelete`) |
| **`feed_requests`** | `id` | `flock_id`, `feed_type` (enum `'Pre-Starter'`, `'Starter'`, `'Finisher'`), `amount_kg` (decimal 10,2), `status` (enum `'pending'`, `'shipped'`, `'completed'`, default `'pending'`) | BelongsTo: `flock` $\rightarrow$ `flocks.id` (`onDelete('cascade')`) |
| **`payrolls`** | `id` | `user_id`, `flock_id`, `base_salary` (integer), `bonus_amount` (integer), `total_paid` (integer), `payment_date` (date) | BelongsTo: `user` $\rightarrow$ `users.id` (`cascade`), `flock` $\rightarrow$ `flocks.id` (`cascade`) |
| **`ovk_inventories`** | `id` | `record_date` (date), `item_name` (string), `category` (enum `'obat'`, `'vaksin'`, `'kimia'`), `transaction_type` (enum `'in'`, `'out'`), `quantity` (decimal 10,2), `unit` (string), `total_price` (integer, nullable), `description` (text, nullable) | Standalone |
| **`cash_flows`** | `id` | `transaction_date` (date), `description` (string), `type` (enum `'in'`, `'out'`), `amount` (integer) | Standalone |
| **`activity_logs`** | `id` | `user_id`, `action` (string), `model_type` (string), `description` (string), `changes` (json, nullable) | BelongsTo: `user` $\rightarrow$ `users.id` (`cascadeOnDelete`) |
| **`system_settings`** | `id` | `key` (string, unique), `value` (string) | Standalone (`global_market_price`, `doc_price`, `feed_price`) |
| **`growth_samplings`** | `id` | `flock_id`, `sampling_date` (date), `week_number` (uint), `average_weight_g` (uint) | BelongsTo: `flock` $\rightarrow$ `flocks.id` (`cascadeOnDelete`) *(orphaned)* |

### 2.2. Default Seeded Accounts (`database/seeders/UserSeeder.php`)

| Role | Name | Email | Password | Base Salary |
| :--- | :--- | :--- | :--- | :--- |
| `owner` | Owner SmartCoop | `owner@smartcoop.com` | `password` | Rp 5.000.000 |
| `worker` | Muhammad Harries Rasyadi | `harries@smartcoop.com` | `password` | Rp 2.800.000 |
| `worker` | Danang Onky Romadhoni | `danang@smartcoop.com` | `password` | Rp 3.000.000 |
| `worker` | Mujawiri Apandie | `mujawiri@smartcoop.com` | `password` | Rp 2.700.000 |

---

## 3. Routing & Access Control Matrix

Defined in `routes/web.php` and `routes/auth.php`.

### 3.1. Public & Auth Routes
- `GET /` $\rightarrow$ `view('welcome')` (Public)
- `GET /login`, `POST /login`, `GET /register`, `POST /register`, `GET /forgot-password`, `POST /forgot-password`, `GET /reset-password/{token}`, `POST /reset-password` $\rightarrow$ `guest` middleware
- `POST /logout`, `GET /verify-email`, `GET /confirm-password`, `PUT /password` $\rightarrow$ `auth` middleware

### 3.2. Authenticated Routes (`middleware(['auth'])`)

| HTTP & URI | Route Name | Controller@Method | Access Control Binding |
| :--- | :--- | :--- | :--- |
| `GET /dashboard` | `dashboard` | Closure (redirects to `flocks.index`) | `auth`, `verified` |
| `POST /settings/update-price` | `settings.updatePrice` | `SystemSettingController@updatePrice` | `auth` only *(no role check)* |
| `GET/PATCH/DELETE /profile` | `profile.{edit,update,destroy}` | `ProfileController` | `auth` |
| `RESOURCE /flocks` | `flocks.*` | `FlockController` | `FlockPolicy` (`create`/`delete`: owner; `view`/`update`: owner or assigned worker) |
| `GET /flocks/{flock}/export-csv` | `flocks.export-csv` | `ReportController@exportCsv` | `FlockPolicy@view` |
| `GET /flocks/{flock}/export-pdf` | `flocks.export-pdf` | `HarvestController@exportPdf` | `FlockPolicy@view` |
| `GET /flocks/{flock}/report/daily-record` | `reports.daily-record` | `ReportController@dailyRecordPreview` | `FlockPolicy@view` |
| `GET /flocks/{flock}/daily-records/create` | `daily_records.create` | `DailyRecordController@create` | `FlockPolicy@update` |
| `POST /flocks/{flock}/daily-records` | `daily_records.store` | `DailyRecordController@store` | `FlockPolicy@update` |
| `PUT /daily-records/{dailyRecord}` | `daily_records.update` | `DailyRecordController@update` | `FlockPolicy@update` |
| `DELETE /daily-records/{dailyRecord}` | `daily_records.destroy` | `DailyRecordController@destroy` | `FlockPolicy@update` |
| `GET /flocks/{flock}/harvests/create` | `harvests.create` | `HarvestController@create` | `FlockPolicy@update` |
| `POST /flocks/{flock}/harvests` | `harvests.store` | `HarvestController@store` | `FlockPolicy@update` |
| `POST /flocks/{flock}/harvests/auto` | `harvests.auto` | `HarvestController@autoStore` | `FlockPolicy@update` |
| `POST /flocks/{flock}/feed-requests` | `feed_requests.store` | `FeedRequestController@store` | `FlockPolicy@update` |
| `POST /feed-requests/{feedRequest}/ship` | `feed_requests.ship` | `FeedRequestController@ship` | Inline `isOwner()` check (403) |
| `POST /feed-requests/{feedRequest}/receive` | `feed_requests.receive` | `FeedRequestController@receive` | `FlockPolicy@update` |
| `RESOURCE /feeds` & `GET /feed` | `feeds.*`, `feed.index` | `FeedInventoryController` | `auth` only *(no role check)* |
| `PATCH /feeds/{feed}/inline-update` | `feeds.inline-update` | `FeedInventoryController@inlineUpdate` | `auth` only *(no role check)* |
| `RESOURCE /ovk` | `ovk.*` | `OvkController` | `auth` only *(no role check)* |
| `RESOURCE /cash-flows` | `cash-flows.*` | `CashFlowController` | `auth` only *(no role check)* |
| `RESOURCE /coops` (`only: index,store,update,destroy`) | `coops.{index,store,update,destroy}` | `CoopController` | `auth`, `can:manage-enterprise` |
| `RESOURCE /workers` (`only: index,store,update,destroy`) | `workers.{index,store,update,destroy}` | `WorkerController` | `auth`, `can:manage-enterprise` |
| `GET /activity-logs` | `activity_logs.index` | `ActivityLogController@index` | `auth`, `can:manage-enterprise` |
| `GET /reports/cash-flow` | `reports.cash-flow` | `ReportController@cashFlowPreview` | `auth`, `can:manage-enterprise` |
| `GET /reports/inventory-audit` | `reports.inventory-audit` | `ReportController@inventoryAuditPreview` | `auth`, `can:manage-enterprise` |

### 3.3. Enterprise Routes (`middleware(['auth', 'can:manage-enterprise'])` — Owner Only)

| HTTP & URI | Route Name | Controller@Method |
| :--- | :--- | :--- |
| `POST /settings/update-price` | `settings.updatePrice` | `SystemSettingController@updatePrice` |
| `RESOURCE /feeds` (`only: index,store`) & `GET /feed` | `feeds.{index,store}`, `feed.index` | `FeedInventoryController` |
| `PATCH /feeds/{feed}/inline-update` | `feeds.inline-update` | `FeedInventoryController@inlineUpdate` |
| `RESOURCE /ovk` (`only: index,store`) | `ovk.{index,store}` | `OvkController` |
| `RESOURCE /cash-flows` (`only: index,store`) | `cash-flows.{index,store}` | `CashFlowController` |
| `RESOURCE /coops` (`only: index,store,update,destroy`) | `coops.{index,store,update,destroy}` | `CoopController` |
| `RESOURCE /workers` (`only: index,store,update,destroy`) | `workers.{index,store,update,destroy}` | `WorkerController` |
| `GET /activity-logs` | `activity_logs.index` | `ActivityLogController@index` |
| `GET /reports/cash-flow` | `reports.cash-flow` | `ReportController@cashFlowPreview` |
| `GET /reports/inventory-audit` | `reports.inventory-audit` | `ReportController@inventoryAuditPreview` |
| `GET /analytics` | `analytics.index` | `AnalyticsController@index` |
| `GET /financial-history` | `financial.history` | `HarvestController@index` |
| `RESOURCE /payrolls` (`only: index,store`) | `payrolls.{index,store}` | `PayrollController` |
| `GET /payrolls/{payroll}/payslip` | `payrolls.payslip` | `PayrollController@payslipPreview` |

---

## 4. File & Component Map

### 4.1. Master Layouts (`resources/views/layouts/`)
- `app.blade.php`: Main authenticated shell, Vite assets, Alpine `darkMode` toggle, anti-flashbang light mode CSS (`#f1f5f9`), FOIT prevention script in `<head>`.
- `navigation.blade.php`: Desktop sidebar + Mobile Alpine drawer. Hides Enterprise & Feeds links from workers via `@if(Auth::user()->isOwner())` and `@can('manage-enterprise')`.
- `print.blade.php`: Clean A4 print layout (`@page { size: A4 portrait; }`).
- `guest.blade.php`: Auth forms wrapper.

### 4.2. Controllers & Required View Variables

| Controller | Action | View Rendered | Variables Passed (`compact` / `view`) |
| :--- | :--- | :--- | :--- |
| `CoopController` | `index` | `coops.index` | `coops` (with `flocks_count`) |
| `FlockController` | `index` | `flocks.index` | `flocks` (with `worker`, `coop`), `workers`, `coops`, `globalMarketPrice` |
| `FlockController` | `create` | `flocks.create` | `workers`, `coops` |
| `FlockController` | `show` | `flocks.show` | `flock`, `dailyRecords`, `harvests`, `totalMortality`, `totalCulling`, `livePopulation`, `totalFeedConsumed`, `totalHarvestSold`, `totalHarvestWeight`, `totalRevenue`, `fcr`, `ip`, `livability`, `avgWeightKg`, `harvestAge`, `chartDates`, `chartMortality`, `chartFeed`, `latestHarvest`, `bepChartData`, `totalCost`, `estimatedRevenue`, `netProfit`, `feedRequests` |
| `FlockController` | `edit` | `flocks.edit` | `flock`, `workers`, `coops` |
| `DailyRecordController` | `create` | `daily_records.create` | `flock` |
| `HarvestController` | `index` | `harvests.history` | `harvests`, `total_net_profit`, `total_revenue`, `total_weight`, `chartLabels`, `chartData` |
| `HarvestController` | `create` | `harvests.create` | `flock`, `livePopulation` |
| `HarvestController` | `exportPdf` | `reports.harvest-preview` | `flock`, `coop`, `harvest`, `livePopulation`, `totalMortality`, `totalCulling`, `totalFeed`, `fcr`, `avgWeight`, `livability`, `harvestAge`, `ip`, `docPrice`, `totalDocCost`, `totalFeedCost`, `grossRevenue`, `laborCost`, `cogs`, `totalExpenses`, `trueNetProfit`, `profitMargin`, `bepPricePerKg` |
| `FeedInventoryController` | `index` | `feed.index` | `inventories`, `totalIn`, `totalOut`, `currentStock`, `activeFilterType`, `periodLabel`, `flocks` |
| `AnalyticsController` | `index` | `analytics.index` | `analyticsData`, `farmAverages` |
| `PayrollController` | `index` | `payrolls.index` | `workers`, `closedFlocks` (enriched with `worker_name`, `worker_base_salary`, `total_chickens_sold`, `total_weight_kg`, `total_feed_kg`, `harvest_age`, `achieved_fcr`, `achieved_ip`, `bonus_percentage`, `bonus_tier_label`, `recommended_bonus`), `payrolls` |
| `PayrollController` | `payslipPreview` | `reports.payslip-preview` | `payroll`, `coop` |
| `OvkController` | `index` | `ovk.index` | `records`, `totalSpending` |
| `CashFlowController` | `index` | `cash_flows.index` | `records`, `total_in`, `total_out`, `balance`, `activeFilterType`, `periodLabel` |
| `WorkerController` | `index` | `workers.index` | `workers` (enriched with `flocks_count`, `closed_flocks_count`, `daily_records_count`, `activity_logs_count`, `discipline_logs_count`, `average_ip`) |
| `ActivityLogController` | `index` | `activity_logs.index` / `partials.table_data` | `logs` |
| `ReportController` | `dailyRecordPreview` | `reports.daily-record-preview` | `flock`, `coop`, `dailyRecords`, `totalMortality`, `totalFeed`, `livePopulation` |
| `ReportController` | `cashFlowPreview` | `reports.cashflow-preview` | `coop` (HQ), `records`, `total_in`, `total_out`, `balance`, `activeFilterType`, `periodLabel` |
| `ReportController` | `inventoryAuditPreview` | `reports.inventory-audit-preview` | `coop` (HQ), `records`, `totalIn`, `totalOut`, `totalValue`, `activeFilterType`, `periodLabel` |

---

## 5. Known Issues & Technical Debt

| ID | Severity | Location | Issue Description | Status |
| :--- | :--- | :--- | :--- | :--- |
| **BUG-01** | 🔴 Critical | `app/Http/Controllers/HarvestController.php` (L78, L132) | Replaced `$flock->dailyRecords()->sum('feed_consumed_kg')` with `DB::raw('feed_pre_starter_kg + feed_starter_kg + feed_finisher_kg')` in `store()` and `autoStore()`. | **Fixed** |
| **BUG-02** | 🔴 Critical | `app/Http/Controllers/ReportController.php` & `inventory-audit-preview.blade.php` | Replaced `'purchase'` filter with `'in'` / `'out'` matching `feed_inventories.transaction_type` enum. | **Fixed** |
| **BUG-03** | 🟡 High | `database/migrations/2026_07_06_190608_add_average_weight_gram_to_daily_records_table.php` (L15) | Updated `->after('feed_consumed_kg')` to `->after('feed_finisher_kg')` for MySQL/MariaDB compatibility. | **Fixed** |
| **BUG-04** | 🟡 Medium | `resources/views/flocks/edit.blade.php` | Created missing `flocks/edit.blade.php` view with `<x-app-layout>`, dark mode support, and `PUT` form to `route('flocks.update', $flock)`. | **Fixed** |
| **BUG-05** | 🟡 Medium | `routes/web.php` | Moved `/settings/update-price`, `/feeds`, `/ovk`, `/cash-flows`, `/workers`, `/activity-logs`, `/reports/cash-flow`, and `/reports/inventory-audit` inside `can:manage-enterprise`. | **Fixed** |
| **BUG-06** | 🟡 Medium | `routes/web.php` | Restricted `Route::resource` with `->only(['index', 'store'])` on `feeds`, `ovk`, `cash-flows`, `payrolls` and `->only(['index', 'store', 'destroy'])` on `workers`. | **Fixed** |
| **DEBT-01**| 🟢 Low | `HarvestController@exportPdf` & `resources/views/` | `exportPdf()` imports `DomPDF` (`Pdf`), yet returns HTML `view('reports.harvest-preview')`. Orphaned PDF templates exist at `resources/views/harvests/pdf.blade.php` and `resources/views/reports/flock_pdf.blade.php`. | **Open** |
| **DEBT-02**| 🟢 Low | `app/Models/GrowthSampling.php` | `growth_samplings` table & model exist without controller or routes (superseded by `daily_records.average_weight_gram`). | **Open** |

---

## 6. Coding Conventions & Rules

1. **Routing & URLs**: Always use named routes via Laravel's `route('name', $params)` helper in Blade and Controllers. Never hardcode paths or `index.php`.
2. **Role Comparisons**: Roles are strictly lowercase strings: `'owner'` and `'worker'`. Prefer `$user->isOwner()`, `$user->isWorker()`, or `@can('manage-enterprise')`.
3. **Authorization Enforcement**:
   - All flock-scoped controller actions MUST call `Gate::authorize('view', $flock)` or `Gate::authorize('update', $flock)`.
   - Owner-only endpoints MUST be placed inside the `Route::middleware(['can:manage-enterprise'])` group in `routes/web.php`.
4. **Feed Calculations (`feed_consumed_kg`)**:
   - **NEVER** call `->sum('feed_consumed_kg')` on a QueryBuilder (`$flock->dailyRecords()->sum(...)`).
   - Either sum over a loaded Collection (`$dailyRecords->sum('feed_consumed_kg')`) or use SQL columns explicitly (`DB::raw('SUM(feed_pre_starter_kg + feed_starter_kg + feed_finisher_kg)')`).
5. **Feed Inventory `transaction_type`**: Always use `'in'` and `'out'` (never `'purchase'`).
6. **Stock Mutators**: When modifying `DailyRecord` feed consumption, always preserve the auto-deduct/revert logic on `flock->stock_{pre_starter,starter,finisher}_kg` and `FeedInventory` records.
7. **Theme & UI**: Respect the Anti-Flashbang light mode (`#f1f5f9` / `#f8fafc`) and Dark mode classes (`dark:*`) in `layouts/app.blade.php`. Use Alpine.js for interactive drawers/modals and Chart.js for data charts.
8. **HRD & Tiered IP Bonus Formula (Phase 2)**:
   - `WorkerController` restricts `update` and `destroy` strictly to accounts where `role === 'worker'`.
   - `PayrollController` calculates tiered IP bonus recommendations from physical feed consumption (`DB::raw('feed_pre_starter_kg + feed_starter_kg + feed_finisher_kg')`):
     - **IP $\ge$ 400 (Sangat Baik)**: `20%` of `base_salary`
     - **IP 350 – 399.99 (Baik)**: `15%` of `base_salary`
     - **IP 300 – 349.99 (Cukup / Standar)**: `10%` of `base_salary`
     - **IP $<$ 300 (Kurang)**: `0%` (`Rp 0`)
   - Owner can still override `base_salary` and `bonus_amount` in the Alpine.js payroll form before submitting to `PayrollController@store`.
9. **Master Coop Units & Dynamic A4 Letterhead (Phase 3 - Option B)**:
   - `coops` table stores unit identity (`name`, `tagline`, `address`, `phone`, `email`, `manager_name`, `is_headquarters`).
   - Flock-specific A4 print views (`daily-record-preview`, `harvest-preview`, `payslip-preview`) pass `$coop = $flock->coop` (or `$payroll->flock?->coop`) to `layouts.print`.
   - Global A4 print views (`cashflow-preview`, `inventory-audit-preview`) use the Headquarters unit (`is_headquarters = true`, default: **Kandang Hanau**) and include the subtitle `"Laporan Rekapitulasi Pusat — Seluruh Unit Kandang"`.
   - `FarmDataSeeder.php` seeds 3 Coop units (`Kandang Hanau` [HQ], `Kandang Seruyan Utama`, `Kandang Plasma C`), links all 12 flocks via `coop_id`, and computes tiered IP bonuses on seeded payrolls. Orphaned `JuneFlockSeeder.php` removed.


