## ADDED Requirements

### Requirement: Manual Rate Fetching

The FxRateService SHALL support manual fetching of rates for admin operations.

#### Scenario: Fetch new rate manually

- **WHEN** calling fetchRateManual(from: USD, to: EUR, date: 2024-06-14) and no rate exists
- **THEN** it SHALL fetch from ECB, create new FxRate record, and return it

#### Scenario: Prevent duplicate manual fetch

- **WHEN** calling fetchRateManual(from: USD, to: EUR, date: 2024-06-14) and rate already exists
- **THEN** it SHALL throw FxRateException with message "Rate already exists for this currency pair and date"

#### Scenario: Manual fetch for replicated rate

- **WHEN** calling fetchRateManual for a date where ECB has no data (weekend)
- **THEN** it SHALL throw FxRateException with message "ECB has no rate for this date. Rate would be replicated."

### Requirement: Rate Re-fetching

The FxRateService SHALL support re-fetching existing rates from ECB.

#### Scenario: Re-fetch existing rate

- **WHEN** calling refetchRate(FxRate $rate) with an existing rate
- **THEN** it SHALL fetch latest data from ECB, update the FxRate record, and return updated record

#### Scenario: Re-fetch updates rate value

- **WHEN** ECB returns different rate value during re-fetch
- **THEN** it SHALL update rate, updated_at timestamp, and return updated record

#### Scenario: Re-fetch updates replication status

- **WHEN** re-fetching a replicated rate (is_replicated=true) and ECB now has data
- **THEN** it SHALL update is_replicated to false, clear replicated_from_date, update rate value

#### Scenario: Re-fetch when ECB still has no data

- **WHEN** re-fetching a rate for weekend date where ECB has no data
- **THEN** it SHALL throw FxRateException: "ECB has no rate for this date"

#### Scenario: Re-fetch returns same value

- **WHEN** ECB returns identical rate value during re-fetch
- **THEN** it SHALL still update updated_at timestamp but keep same rate value
