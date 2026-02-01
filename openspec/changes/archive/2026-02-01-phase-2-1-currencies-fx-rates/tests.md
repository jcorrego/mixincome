## 1. Currency Model

- [ ] 1.1 [Unit] Currency has required fields: code, name, symbol, decimal_places (Scenario: Currency has required fields)
- [ ] 1.2 [Unit] Currency code is unique and rejects duplicates (Scenario: Currency code uniqueness)
- [ ] 1.3 [Unit] USD currency has correct metadata (Scenario: USD currency)
- [ ] 1.4 [Unit] EUR currency has correct metadata (Scenario: EUR currency)
- [ ] 1.5 [Unit] COP currency has correct metadata with 0 decimals (Scenario: COP currency)
- [ ] 1.6 [Unit] Currency enum provides correct string codes (Scenario: Enum provides currency codes)
- [ ] 1.7 [Unit] Currency enum decimals() returns correct values (Scenario: Enum provides decimal places)
- [ ] 1.8 [Unit] Currency enum symbol() returns correct symbols (Scenario: Enum provides symbols)
- [ ] 1.9 [Unit] Currency has many source FxRates relationship (Scenario: Currency has source FxRates)
- [ ] 1.10 [Unit] Currency has many target FxRates relationship (Scenario: Currency has target FxRates)

## 2. FxRate Model

- [ ] 2.1 [Unit] FxRate has required fields including rate with 8 decimal precision (Scenario: FxRate has required fields)
- [ ] 2.2 [Unit] FxRate stores small rates correctly (Scenario: FxRate rate precision)
- [ ] 2.3 [Unit] FxRate enforces unique constraint on currency pair + date (Scenario: Unique constraint on currency pair and date)
- [ ] 2.4 [Unit] FxRate allows different dates for same currency pair (Scenario: Different dates allowed)
- [ ] 2.5 [Unit] FxRate allows different currency pairs on same date (Scenario: Different currency pairs allowed on same date)
- [ ] 2.6 [Unit] Original rate has is_replicated=false and null replicated_from_date (Scenario: Original rate from source)
- [ ] 2.7 [Unit] Replicated rate has is_replicated=true and valid replicated_from_date (Scenario: Replicated rate)
- [ ] 2.8 [Unit] FxRate belongs to source currency (Scenario: FxRate belongs to source currency)
- [ ] 2.9 [Unit] FxRate belongs to target currency (Scenario: FxRate belongs to target currency)
- [ ] 2.10 [Unit] FxRate factory creates valid rate (Scenario: Default factory creates valid rate)
- [ ] 2.11 [Unit] FxRate factory replicated state works correctly (Scenario: Factory supports replicated state)

## 3. FxRateService - Find Rates

- [ ] 3.1 [Unit] findOrFetchRate returns existing rate without API call (Scenario: Rate exists for exact date)
- [ ] 3.2 [Unit] findOrFetchRate fetches from ECB when rate not cached (Scenario: Rate does not exist)

## 4. FxRateService - Fetch from ECB

- [ ] 4.1 [Unit] Creates FxRate with source=ecb and is_replicated=false on ECB success (Scenario: ECB returns valid rate)
- [ ] 4.2 [Unit] Falls back to replication when ECB has no data for date (Scenario: ECB rate not available)

## 5. FxRateService - Replication

- [ ] 5.1 [Unit] Replicates from immediate previous day (Scenario: Replicate from immediate previous day)
- [ ] 5.2 [Unit] Replicates across multiple days finding Friday rate for Sunday (Scenario: Replicate across multiple days)
- [ ] 5.3 [Unit] Searches maximum 7 days back for replication (Scenario: Search limit for replication)
- [ ] 5.4 [Unit] Throws RateNotAvailableException when no rate within 7 days (Scenario: No rate available within 7 days)

## 6. FxRateService - Calculations

- [ ] 6.1 [Unit] Converts amount using rate correctly (Scenario: Convert amount using rate)
- [ ] 6.2 [Unit] Rounds to 0 decimals for COP (Scenario: Respect decimal places)
- [ ] 6.3 [Unit] Rounds to 2 decimals for EUR and USD (Scenario: Respect decimal places for EUR/USD)

## 7. FxRateService - Batch Sync

- [ ] 7.1 [Unit] Syncs rates for entire date range (Scenario: Sync date range)
- [ ] 7.2 [Unit] Skips existing rates during sync (Scenario: Skip existing rates during sync)

## 8. EcbApiService

- [ ] 8.1 [Unit] fetchRate returns float rate value (Scenario: Fetch single rate)
- [ ] 8.2 [Unit] Calculates inverse for non-EUR base rates (Scenario: ECB base currency conversion)
- [ ] 8.3 [Unit] Uses correct ECB SDMX endpoint (Scenario: Correct endpoint format)
- [ ] 8.4 [Unit] Parses ECB response correctly (Scenario: Response parsing)
- [ ] 8.5 [Unit] Throws RateNotAvailableException for weekend/holiday (Scenario: Rate not available for date)
- [ ] 8.6 [Unit] Retries with exponential backoff on timeout (Scenario: API timeout)
- [ ] 8.7 [Unit] Throws EcbApiException on HTTP errors (Scenario: API returns error status)
- [ ] 8.8 [Unit] Returns direct rate for EUR to other currency (Scenario: EUR to other currency)
- [ ] 8.9 [Unit] Calculates inverse for other currency to EUR (Scenario: Other currency to EUR)
- [ ] 8.10 [Unit] Calculates cross rates for non-EUR pairs (Scenario: Cross rates)

## 9. Database & Seeder

- [ ] 9.1 [Feature] currencies migration creates table with correct schema
- [ ] 9.2 [Feature] fx_rates migration creates table with correct schema and indexes
- [ ] 9.3 [Feature] CurrencySeeder seeds USD, EUR, COP correctly
