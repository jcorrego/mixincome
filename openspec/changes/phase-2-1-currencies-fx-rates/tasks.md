## 1. Database Migrations

- [ ] 1.1 Create currencies table migration with: code, name, symbol, decimal_places
- [ ] 1.2 Create fx_rates table migration with: from_currency_id, to_currency_id, date, rate, source, is_replicated, replicated_from_date, unique constraint, indexes
- [ ] 1.3 Run migrations to verify schema
      → Tests passing: 9.1, 9.2

## 2. Currency Enum

- [ ] 2.1 Create Currency enum in `app/Enums/Currency.php` with USD, EUR, COP cases
- [ ] 2.2 Add decimals() method returning correct decimal places per currency
- [ ] 2.3 Add symbol() method returning correct symbols per currency
      → Tests passing: 1.6, 1.7, 1.8

## 3. Currency Model

- [ ] 3.1 Create Currency model with fillable fields and casts
- [ ] 3.2 Add sourceFxRates() hasMany relationship
- [ ] 3.3 Add targetFxRates() hasMany relationship
- [ ] 3.4 Create CurrencyFactory with realistic defaults
      → Tests passing: 1.1, 1.2, 1.9, 1.10

## 4. Currency Seeder

- [ ] 4.1 Create CurrencySeeder with USD, EUR, COP data
- [ ] 4.2 Run seeder to verify data
      → Tests passing: 1.3, 1.4, 1.5, 9.3

## 5. FxRate Model

- [ ] 5.1 Create FxRate model with fillable fields and casts
- [ ] 5.2 Add fromCurrency() belongsTo relationship
- [ ] 5.3 Add toCurrency() belongsTo relationship
- [ ] 5.4 Create FxRateFactory with realistic defaults and currency relationships
- [ ] 5.5 Add replicated() factory state for testing replication scenarios
      → Tests passing: 2.1, 2.2, 2.3, 2.4, 2.5, 2.6, 2.7, 2.8, 2.9, 2.10, 2.11

## 6. Custom Exceptions

- [ ] 6.1 Create RateNotAvailableException in `app/Exceptions/`
- [ ] 6.2 Create EcbApiException in `app/Exceptions/`

## 7. EcbApiService

- [ ] 7.1 Create EcbApiService in `app/Services/`
- [ ] 7.2 Implement fetchRate(from, to, date) with ECB SDMX endpoint
- [ ] 7.3 Implement response parsing for ECB XML/JSON format
- [ ] 7.4 Add inverse rate calculation for non-EUR base
- [ ] 7.5 Add cross-rate calculation for non-EUR pairs (via EUR)
- [ ] 7.6 Add retry logic with exponential backoff (3 attempts)
- [ ] 7.7 Add proper exception handling (RateNotAvailableException, EcbApiException)
      → Tests passing: 8.1, 8.2, 8.3, 8.4, 8.5, 8.6, 8.7, 8.8, 8.9, 8.10

## 8. FxRateService - Core Methods

- [ ] 8.1 Create FxRateService in `app/Services/`
- [ ] 8.2 Implement findOrFetchRate(from, to, date) - find existing rate
- [ ] 8.3 Integrate EcbApiService for fetching when not cached
- [ ] 8.4 Create FxRate record with source='ecb' and is_replicated=false on ECB success
      → Tests passing: 3.1, 3.2, 4.1

## 9. FxRateService - Replication

- [ ] 9.1 Implement replicateFromPreviousDay(from, to, date) method
- [ ] 9.2 Add 7-day lookback search for previous rate
- [ ] 9.3 Create replicated FxRate with is_replicated=true and replicated_from_date set
- [ ] 9.4 Throw RateNotAvailableException when no rate within 7 days
- [ ] 9.5 Integrate replication fallback in findOrFetchRate when ECB fails
      → Tests passing: 4.2, 5.1, 5.2, 5.3, 5.4

## 10. FxRateService - Calculations

- [ ] 10.1 Implement convert(amount, from, to, date) method
- [ ] 10.2 Add decimal place rounding based on target currency
      → Tests passing: 6.1, 6.2, 6.3

## 11. FxRateService - Batch Sync

- [ ] 11.1 Implement syncRates(startDate, endDate, from, to) method
- [ ] 11.2 Add skip logic for dates with existing rates
      → Tests passing: 7.1, 7.2

## 12. Refactor & Cleanup

- [ ] 12.1 Run full test suite, verify all tests green
- [ ] 12.2 Run `vendor/bin/pint --dirty` to fix formatting
- [ ] 12.3 Run `composer test` (includes type coverage + static analysis)
- [ ] 12.4 Update MIGRATION.md to mark Phase 2.1 as complete
