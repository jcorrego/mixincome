## ADDED Requirements

### Requirement: FxRate Model

The system SHALL provide an FxRate model storing historical exchange rates between currency pairs.

#### Scenario: FxRate has required fields

- **WHEN** an FxRate record exists
- **THEN** it SHALL have: from_currency_id, to_currency_id, date, rate (DECIMAL 12,8), source (VARCHAR 50), is_replicated (BOOLEAN), replicated_from_date (DATE nullable)

#### Scenario: FxRate rate precision

- **WHEN** storing a small exchange rate (e.g., COP to USD)
- **THEN** the rate field SHALL support up to 8 decimal places (e.g., 0.00025000)

### Requirement: FxRate Uniqueness

The system SHALL enforce one rate per currency pair per date.

#### Scenario: Unique constraint on currency pair and date

- **WHEN** attempting to create an FxRate with the same from_currency_id, to_currency_id, and date as an existing record
- **THEN** the system SHALL reject the record with a unique constraint violation

#### Scenario: Different dates allowed

- **WHEN** creating FxRate records for the same currency pair but different dates
- **THEN** the system SHALL allow both records

#### Scenario: Different currency pairs allowed on same date

- **WHEN** creating FxRate records for different currency pairs on the same date
- **THEN** the system SHALL allow both records

### Requirement: FxRate Replication Tracking

The system SHALL track when rates are replicated from previous days.

#### Scenario: Original rate from source

- **WHEN** an FxRate is fetched directly from ECB for a given date
- **THEN** is_replicated SHALL be false and replicated_from_date SHALL be null

#### Scenario: Replicated rate

- **WHEN** an FxRate is created by copying a previous day's rate (weekend/holiday)
- **THEN** is_replicated SHALL be true and replicated_from_date SHALL contain the original rate's date

### Requirement: FxRate Relationships

The FxRate model SHALL have relationships to Currency.

#### Scenario: FxRate belongs to source currency

- **WHEN** querying an FxRate's fromCurrency relationship
- **THEN** it SHALL return the Currency record matching from_currency_id

#### Scenario: FxRate belongs to target currency

- **WHEN** querying an FxRate's toCurrency relationship
- **THEN** it SHALL return the Currency record matching to_currency_id

### Requirement: FxRate Factory

The system SHALL provide a factory for creating FxRate test data.

#### Scenario: Default factory creates valid rate

- **WHEN** using FxRate::factory()->create()
- **THEN** it SHALL create a valid FxRate with currencies and realistic rate

#### Scenario: Factory supports replicated state

- **WHEN** using FxRate::factory()->replicated()->create()
- **THEN** it SHALL create an FxRate with is_replicated=true and a valid replicated_from_date
