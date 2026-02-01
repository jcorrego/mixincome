## ADDED Requirements

### Requirement: Display Currency List

The Currency Admin UI SHALL display all available currencies.

#### Scenario: View currency index page

- **WHEN** an authenticated user navigates to `/management/currencies`
- **THEN** the system SHALL display all currencies (USD, EUR, COP) with code, name, symbol, and decimal_places

#### Scenario: Unauthenticated access denied

- **WHEN** an unauthenticated user attempts to access `/management/currencies`
- **THEN** the system SHALL redirect to login page

### Requirement: Display Currency Detail

The Currency Admin UI SHALL display detailed information for a specific currency.

#### Scenario: View currency detail page

- **WHEN** an authenticated user navigates to `/management/currencies/{currency}`
- **THEN** the system SHALL display currency metadata and all associated FX rates

#### Scenario: Display rates as source currency

- **WHEN** viewing currency detail for EUR
- **THEN** the system SHALL display all rates where EUR is the from_currency (e.g., EUR→USD, EUR→COP)

#### Scenario: Display rates as target currency

- **WHEN** viewing currency detail for USD
- **THEN** the system SHALL display all rates where USD is the to_currency (e.g., EUR→USD, COP→USD)

#### Scenario: Display rate metadata

- **WHEN** displaying FX rates
- **THEN** each rate SHALL show: date, rate value, source (ecb), is_replicated flag, replicated_from_date (if applicable)

### Requirement: Fetch New Rate

The Currency Admin UI SHALL allow fetching new rates for specific dates.

#### Scenario: Fetch rate for valid date

- **WHEN** user submits fetch rate form with from_currency, to_currency, and date
- **THEN** the system SHALL call FxRateService to fetch from ECB and display success message with new rate

#### Scenario: Fetch rate for weekend date

- **WHEN** user fetches rate for a Saturday or Sunday
- **THEN** the system SHALL attempt ECB fetch, replicate from previous day if ECB unavailable, and display result

#### Scenario: Duplicate rate validation

- **WHEN** user attempts to fetch a rate that already exists for that currency pair and date
- **THEN** the system SHALL display error message: "Rate already exists. Use 'Re-fetch' to overwrite."

#### Scenario: Future date validation

- **WHEN** user submits a date in the future
- **THEN** the system SHALL reject with validation error: "Date cannot be in the future."

#### Scenario: Same currency validation

- **WHEN** user submits from_currency = to_currency (e.g., USD→USD)
- **THEN** the system SHALL reject with validation error: "From and to currencies must be different."

#### Scenario: ECB API failure handling

- **WHEN** ECB API fails during fetch
- **THEN** the system SHALL display user-friendly error message with retry option

### Requirement: Re-fetch Existing Rate

The Currency Admin UI SHALL allow re-fetching existing rates from ECB.

#### Scenario: Re-fetch rate successfully

- **WHEN** user clicks "Re-fetch" on an existing rate
- **THEN** the system SHALL call ECB API again, update the FxRate record with new data, and display success message

#### Scenario: Re-fetch with different ECB value

- **WHEN** ECB returns a different rate value than stored
- **THEN** the system SHALL update the rate and display: "Rate updated from {old} to {new}"

#### Scenario: Re-fetch with same ECB value

- **WHEN** ECB returns the same rate value
- **THEN** the system SHALL display: "Rate unchanged, ECB value matches existing rate"

#### Scenario: Re-fetch replicated rate

- **WHEN** user re-fetches a rate where is_replicated=true
- **THEN** the system SHALL attempt ECB fetch and update to is_replicated=false if ECB now has data

### Requirement: Realtime Feedback

The Currency Admin UI SHALL provide real-time feedback during async operations.

#### Scenario: Loading state during fetch

- **WHEN** user submits fetch rate form
- **THEN** the system SHALL display loading spinner and disable form until operation completes

#### Scenario: Loading state during re-fetch

- **WHEN** user clicks re-fetch button
- **THEN** the system SHALL display loading indicator on that specific rate row until operation completes

#### Scenario: Success notification

- **WHEN** fetch or re-fetch succeeds
- **THEN** the system SHALL display success message with rate details for 5 seconds

#### Scenario: Error notification

- **WHEN** fetch or re-fetch fails
- **THEN** the system SHALL display error message with reason and suggested action
