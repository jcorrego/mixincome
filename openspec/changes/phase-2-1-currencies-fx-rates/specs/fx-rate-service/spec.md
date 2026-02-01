## ADDED Requirements

### Requirement: Find Existing Rate

The FxRateService SHALL find existing rates from the database.

#### Scenario: Rate exists for exact date

- **WHEN** calling findOrFetchRate(from: USD, to: EUR, date: 2024-06-14) and a rate exists for that exact date
- **THEN** it SHALL return the existing FxRate record without calling ECB API

#### Scenario: Rate does not exist

- **WHEN** calling findOrFetchRate(from: USD, to: EUR, date: 2024-06-14) and no rate exists
- **THEN** it SHALL attempt to fetch from ECB API

### Requirement: Fetch Rate from ECB

The FxRateService SHALL fetch rates from ECB when not cached.

#### Scenario: ECB returns valid rate

- **WHEN** ECB API returns a valid rate for the requested date
- **THEN** it SHALL create an FxRate record with source='ecb', is_replicated=false

#### Scenario: ECB rate not available

- **WHEN** ECB API indicates no rate available for the date (weekend/holiday)
- **THEN** it SHALL fall back to replication from previous day

### Requirement: Replicate from Previous Day

The FxRateService SHALL replicate rates when ECB data is unavailable.

#### Scenario: Replicate from immediate previous day

- **WHEN** no rate exists for 2024-06-15 (Saturday) but exists for 2024-06-14 (Friday)
- **THEN** it SHALL create a new FxRate for 2024-06-15 with the Friday rate, is_replicated=true, replicated_from_date='2024-06-14'

#### Scenario: Replicate across multiple days

- **WHEN** no rate exists for 2024-06-16 (Sunday) and 2024-06-15 (Saturday) but exists for 2024-06-14 (Friday)
- **THEN** it SHALL create a new FxRate for 2024-06-16 with the Friday rate, replicated_from_date='2024-06-14'

#### Scenario: Search limit for replication

- **WHEN** searching for a previous rate to replicate
- **THEN** it SHALL search up to 7 days back maximum

#### Scenario: No rate available within 7 days

- **WHEN** no rate exists within 7 days of the requested date
- **THEN** it SHALL throw a RateNotAvailableException

### Requirement: Rate Calculation

The FxRateService SHALL provide accurate currency conversion calculations.

#### Scenario: Convert amount using rate

- **WHEN** converting 1500 EUR to USD with rate 1.08
- **THEN** it SHALL return 1620.00 (1500 * 1.08)

#### Scenario: Respect decimal places

- **WHEN** converting to COP (0 decimal places)
- **THEN** it SHALL round to whole number

#### Scenario: Respect decimal places for EUR/USD

- **WHEN** converting to EUR or USD (2 decimal places)
- **THEN** it SHALL round to 2 decimal places

### Requirement: Batch Sync

The FxRateService SHALL support batch syncing of rates.

#### Scenario: Sync date range

- **WHEN** calling syncRates(startDate: 2024-01-01, endDate: 2024-01-31) for USD→EUR
- **THEN** it SHALL fetch and store rates for all business days in the range

#### Scenario: Skip existing rates during sync

- **WHEN** syncing a date range where some rates already exist
- **THEN** it SHALL skip existing dates and only fetch missing ones
