## Why

MixIncome requires multi-currency support for tax reporting across Spain, USA, and Colombia. Transactions occur in USD, EUR, and COP, but tax forms require amounts in the jurisdiction's currency (EUR for Spain IRPF, USD for US forms, COP for Colombia). Without a currency conversion system with historical exchange rates, accurate tax preparation is impossible.

## What Changes

- **New `Currency` model**: Represents supported currencies (USD, EUR, COP) with metadata (symbol, decimal places)
- **New `FxRate` model**: Stores historical exchange rates from official sources (ECB initially)
- **New `FxRateService`**: Fetches rates from ECB API, handles weekend/holiday replication, provides conversion calculations
- **New `EcbApiService`**: Integration with European Central Bank exchange rate API
- **Currency seeder**: Pre-populates USD, EUR, COP with correct metadata
- **Replication logic**: Automatically fills weekend/holiday gaps using previous day's rate with audit trail

## Capabilities

### New Capabilities

- `currency-management`: Currency model with code, name, symbol, and decimal places. Seeded with USD (2 decimals), EUR (2 decimals), COP (0 decimals).
- `fx-rate-management`: FxRate model storing historical rates between currency pairs. Single rate per date (last write wins). Tracks replication status for weekend/holiday gap-filling.
- `fx-rate-service`: Service to find or fetch rates, replicate from previous days, and sync batches from ECB.
- `ecb-api-integration`: ECB API client for fetching official EUR-based exchange rates.

### Modified Capabilities

_None - this is new functionality._

## Impact

- **Database**: Two new tables (`currencies`, `fx_rates`)
- **Models**: `Currency`, `FxRate` with relationships
- **Services**: `FxRateService`, `EcbApiService`
- **Seeders**: `CurrencySeeder`
- **Future dependencies**: Transactions (Phase 2.2) will use this for `amount_usd`, `amount_eur`, `amount_cop` conversion
- **External API**: ECB Statistical Data Warehouse API (public, no auth required)
