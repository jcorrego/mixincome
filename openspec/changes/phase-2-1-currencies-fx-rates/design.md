## Context

MixIncome processes financial transactions in multiple currencies (USD, EUR, COP) for tax reporting across Spain, USA, and Colombia. Each jurisdiction requires amounts in its local currency. Currently there is no currency or exchange rate infrastructure.

**Current state:** No currency models exist. Jurisdiction model has `default_currency` string field but no relationships.

**Constraints:**
- Only 3 currencies needed: USD, EUR, COP (hard-coded, not dynamic)
- ECB is the primary rate source (publishes EUR-based rates)
- Weekends/holidays have no ECB data - must replicate from previous business day
- Manual overrides happen at transaction level, not rate level
- This phase creates infrastructure only; Transactions come in Phase 2.2

## Goals / Non-Goals

**Goals:**
- Currency model with metadata (symbol, decimal places)
- FxRate model for historical exchange rates with replication tracking
- FxRateService for fetching/caching/replicating rates
- EcbApiService for ECB API integration
- Seeder for USD, EUR, COP

**Non-Goals:**
- Transaction model (Phase 2.2)
- CurrencyConversionService (Phase 2.2)
- UI for managing currencies or rates
- Manual rate entry (rates always from official sources)
- Additional currencies beyond USD/EUR/COP
- Real-time rate fetching (batch/on-demand only)

## Decisions

### 1. Hard-coded Currencies vs Dynamic Table

**Decision:** Use database table with seeder for 3 currencies

**Alternatives considered:**
- PHP Enum only: Simple but can't eager-load relationships or store metadata
- Dynamic table with UI: Over-engineering for fixed 3 currencies

**Rationale:** Table allows `Jurisdiction.default_currency_id` foreign key and future-proofs for additional currencies without major refactoring.

### 2. FxRate Structure: Bidirectional vs Unidirectional

**Decision:** Store rates one-way per currency pair, one rate per date

**Schema:**
```sql
UNIQUE (from_currency_id, to_currency_id, date)
```

**Alternatives considered:**
- Bidirectional (EUR→USD and USD→EUR both stored): Redundant, inconsistency risk
- Star schema (all vs USD base): Requires triangulation for EUR→COP

**Rationale:** ECB provides EUR as base. Store direct pairs as needed. When converting EUR→USD, fetch that exact pair.

### 3. Weekend/Holiday Handling

**Decision:** Replicate previous day's rate with tracking fields

**Schema:**
```php
is_replicated: boolean (default false)
replicated_from_date: date (nullable)
```

**Rationale:** Tax compliance requires knowing if rate was actual or replicated. Searching back up to 7 days handles long holidays.

### 4. Rate Precision

**Decision:** `DECIMAL(12, 8)` for rate column

**Rationale:** COP rates are very small (1 COP ≈ 0.00025 USD). 8 decimal places ensures precision. 12 total digits handles large multipliers.

### 5. Source Extensibility

**Decision:** Use VARCHAR(50) for `source` field instead of enum

**Alternatives considered:**
- PHP Enum + DB enum: Requires migration for new sources
- Enum with limited values: Blocks future ECB alternatives

**Rationale:** Starting with 'ecb' only, but Fed, BoE, or other sources may be added later without schema changes.

### 6. Service Architecture

**Decision:** Two services with single responsibility

```
EcbApiService
  └── fetchRate(from, to, date): float
  └── fetchRatesForDateRange(from, to, startDate, endDate): array

FxRateService
  └── findOrFetchRate(from, to, date): FxRate
  └── replicateFromPreviousDay(from, to, date): FxRate
  └── syncRates(startDate, endDate): void
```

**Rationale:** EcbApiService is pure API client (replaceable). FxRateService handles business logic (caching, replication, database).

## Database Schema

### currencies

```sql
CREATE TABLE currencies (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  code VARCHAR(3) UNIQUE NOT NULL,
  name VARCHAR(50) NOT NULL,
  symbol VARCHAR(5) NOT NULL,
  decimal_places TINYINT UNSIGNED NOT NULL DEFAULT 2,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
);
```

### fx_rates

```sql
CREATE TABLE fx_rates (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  from_currency_id BIGINT UNSIGNED NOT NULL,
  to_currency_id BIGINT UNSIGNED NOT NULL,
  date DATE NOT NULL,
  rate DECIMAL(12, 8) NOT NULL,
  source VARCHAR(50) NOT NULL,
  is_replicated BOOLEAN NOT NULL DEFAULT FALSE,
  replicated_from_date DATE NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  
  FOREIGN KEY (from_currency_id) REFERENCES currencies(id) ON DELETE RESTRICT,
  FOREIGN KEY (to_currency_id) REFERENCES currencies(id) ON DELETE RESTRICT,
  UNIQUE KEY unique_rate (from_currency_id, to_currency_id, date),
  INDEX idx_conversion (from_currency_id, to_currency_id, date)
);
```

## ECB API Integration

**Endpoint:** ECB Statistical Data Warehouse (SDMX)
```
https://data-api.ecb.europa.eu/service/data/EXR/D.{currency}.EUR.SP00.A
```

**Response format:** XML/JSON with daily rates

**Rate calculation:** ECB publishes EUR as base. For USD→EUR:
- ECB gives: 1 EUR = 1.08 USD (inverted)
- We store: USD→EUR rate = 1/1.08 = 0.926

**Error handling:**
- Rate not available for date → Replicate from previous day
- API timeout → Retry with exponential backoff (3 attempts)
- API down → Throw exception, let caller handle

## Risks / Trade-offs

| Risk | Mitigation |
|------|------------|
| ECB API unavailable | Replication fills gaps; sync during off-peak hours |
| Rate precision loss | DECIMAL(12,8) provides sufficient precision for all supported currencies |
| Weekend rate audits | `is_replicated` + `replicated_from_date` provide full traceability |
| Future currency additions | Requires migration for Transaction columns, but Currency/FxRate tables are flexible |
| ECB only provides EUR base | Calculate inverse rates or cross-rates as needed |

## Open Questions

_None - design decisions validated during exploration phase._
