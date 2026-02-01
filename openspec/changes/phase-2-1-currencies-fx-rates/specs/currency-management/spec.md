## ADDED Requirements

### Requirement: Currency Model

The system SHALL provide a Currency model representing supported currencies with their metadata.

#### Scenario: Currency has required fields

- **WHEN** a Currency record exists
- **THEN** it SHALL have: code (VARCHAR 3, unique), name (VARCHAR 50), symbol (VARCHAR 5), decimal_places (TINYINT, default 2)

#### Scenario: Currency code uniqueness

- **WHEN** attempting to create a Currency with a code that already exists
- **THEN** the system SHALL reject the record with a unique constraint violation

### Requirement: Supported Currencies

The system SHALL support exactly three currencies: USD, EUR, and COP.

#### Scenario: USD currency

- **WHEN** the USD currency is seeded
- **THEN** it SHALL have: code='USD', name='United States Dollar', symbol='$', decimal_places=2

#### Scenario: EUR currency

- **WHEN** the EUR currency is seeded
- **THEN** it SHALL have: code='EUR', name='Euro', symbol='€', decimal_places=2

#### Scenario: COP currency

- **WHEN** the COP currency is seeded
- **THEN** it SHALL have: code='COP', name='Colombian Peso', symbol='$', decimal_places=0

### Requirement: Currency Enum Helper

The system SHALL provide a Currency enum for type-safe currency references in code.

#### Scenario: Enum provides currency codes

- **WHEN** using the Currency enum
- **THEN** it SHALL have cases: USD, EUR, COP with string values matching the codes

#### Scenario: Enum provides decimal places

- **WHEN** calling decimals() on a Currency enum case
- **THEN** it SHALL return: 2 for USD, 2 for EUR, 0 for COP

#### Scenario: Enum provides symbols

- **WHEN** calling symbol() on a Currency enum case
- **THEN** it SHALL return: '$' for USD, '€' for EUR, '$' for COP

### Requirement: Currency Relationships

The Currency model SHALL support relationships with FxRate.

#### Scenario: Currency has source FxRates

- **WHEN** querying a Currency's outgoing rates
- **THEN** it SHALL return all FxRate records where from_currency_id matches

#### Scenario: Currency has target FxRates

- **WHEN** querying a Currency's incoming rates
- **THEN** it SHALL return all FxRate records where to_currency_id matches
