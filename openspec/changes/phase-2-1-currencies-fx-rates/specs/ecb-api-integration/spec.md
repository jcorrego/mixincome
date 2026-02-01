## ADDED Requirements

### Requirement: ECB API Client

The system SHALL provide an EcbApiService for fetching exchange rates from ECB.

#### Scenario: Fetch single rate

- **WHEN** calling fetchRate(from: 'USD', to: 'EUR', date: 2024-06-14)
- **THEN** it SHALL return the exchange rate as a float

#### Scenario: ECB base currency conversion

- **WHEN** ECB publishes rates with EUR as base (e.g., 1 EUR = 1.08 USD)
- **THEN** the service SHALL calculate the inverse for USD→EUR (1/1.08 = 0.926)

### Requirement: ECB API Endpoint

The EcbApiService SHALL use the correct ECB Statistical Data Warehouse endpoint.

#### Scenario: Correct endpoint format

- **WHEN** requesting a rate
- **THEN** it SHALL call the ECB SDMX endpoint at data-api.ecb.europa.eu

#### Scenario: Response parsing

- **WHEN** ECB returns data in XML/JSON format
- **THEN** it SHALL parse the response and extract the rate value

### Requirement: Error Handling

The EcbApiService SHALL handle API errors gracefully.

#### Scenario: Rate not available for date

- **WHEN** ECB has no data for the requested date (weekend/holiday)
- **THEN** it SHALL throw a RateNotAvailableException

#### Scenario: API timeout

- **WHEN** ECB API does not respond within timeout period
- **THEN** it SHALL retry up to 3 times with exponential backoff

#### Scenario: API returns error status

- **WHEN** ECB API returns HTTP 4xx or 5xx status
- **THEN** it SHALL throw an EcbApiException with the error details

### Requirement: Rate Direction Handling

The EcbApiService SHALL handle different rate directions.

#### Scenario: EUR to other currency

- **WHEN** fetching EUR→USD rate
- **THEN** it SHALL use ECB's direct rate (EUR is ECB's base)

#### Scenario: Other currency to EUR

- **WHEN** fetching USD→EUR rate
- **THEN** it SHALL calculate inverse of ECB's EUR/USD rate

#### Scenario: Cross rates (non-EUR pairs)

- **WHEN** fetching USD→COP rate
- **THEN** it SHALL calculate via EUR: (USD→EUR) * (EUR→COP)
