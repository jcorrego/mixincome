## 1. Database Migrations

- [x] 1.1 Create currencies table migration with: code, name, symbol, decimal_places
- [x] 1.2 Create fx_rates table migration with: from_currency_id, to_currency_id, date, rate, source, is_replicated, replicated_from_date, unique constraint, indexes
- [x] 1.3 Run migrations to verify schema
      → Tests passing: 9.1, 9.2

## 2. Currency Enum

- [x] 2.1 Create Currency enum in `app/Enums/Currency.php` with USD, EUR, COP cases
- [x] 2.2 Add decimals() method returning correct decimal places per currency
- [x] 2.3 Add symbol() method returning correct symbols per currency
      → Tests passing: 1.6, 1.7, 1.8

## 3. Currency Model

- [x] 3.1 Create Currency model with fillable fields and casts
- [x] 3.2 Add sourceFxRates() hasMany relationship
- [x] 3.3 Add targetFxRates() hasMany relationship
- [x] 3.4 Create CurrencyFactory with realistic defaults
      → Tests passing: 1.1, 1.2, 1.9, 1.10

## 4. Currency Seeder

- [x] 4.1 Create CurrencySeeder with USD, EUR, COP data
- [x] 4.2 Run seeder to verify data
      → Tests passing: 1.3, 1.4, 1.5, 9.3

## 5. FxRate Model

- [x] 5.1 Create FxRate model with fillable fields and casts
- [x] 5.2 Add fromCurrency() belongsTo relationship
- [x] 5.3 Add toCurrency() belongsTo relationship
- [x] 5.4 Create FxRateFactory with realistic defaults and currency relationships
- [x] 5.5 Add replicated() factory state for testing replication scenarios
      → Tests passing: 2.1, 2.2, 2.3, 2.4, 2.5, 2.6, 2.7, 2.8, 2.9, 2.10, 2.11

## 6. Custom Exceptions

- [x] 6.1 Create FxRateException in `app/Exceptions/` (consolidated exception for API and rate errors)

## 7. EcbApiService

- [x] 7.1 Create EcbApiService in `app/Services/`
- [x] 7.2 Implement fetchRate(from, to, date) with ECB SDMX endpoint
- [x] 7.3 Implement response parsing for ECB XML/JSON format
- [x] 7.4 Add inverse rate calculation for non-EUR base
- [x] 7.5 Add cross-rate calculation for non-EUR pairs (via EUR)
- [x] 7.6 Add retry logic with exponential backoff (3 attempts)
- [x] 7.7 Add proper exception handling (FxRateException)
      → Tests passing: 8.1, 8.2, 8.3, 8.4, 8.5, 8.6, 8.7, 8.8, 8.9, 8.10

## 8. FxRateService - Core Methods

- [x] 8.1 Create FxRateService in `app/Services/`
- [x] 8.2 Implement findRate(from, to, date) - find existing rate
- [x] 8.3 Implement fetchRate(from, to, date) - fetch via EcbApiService
- [x] 8.4 Create FxRate record with source='ecb' and is_replicated=false on ECB success
      → Tests passing: 3.1, 3.2, 4.1

## 9. FxRateService - Replication

- [x] 9.1 Implement replicateRate(from, to, date) method
- [x] 9.2 Add 7-day lookback search for previous rate
- [x] 9.3 Create replicated FxRate with is_replicated=true and replicated_from_date set
- [x] 9.4 Return null when no rate within 7 days
      → Tests passing: 4.2, 5.1, 5.2, 5.3, 5.4

## 10. FxRateService - Calculations

- [x] 10.1 Implement convert(amount, from, to, date) method
- [x] 10.2 Add decimal place rounding based on target currency
      → Tests passing: 6.1, 6.2, 6.3

## 11. FxRateService - Batch Sync

- [ ] 11.1 Implement syncRates(startDate, endDate, from, to) method (DEFERRED - not in scope for MVP)
- [ ] 11.2 Add skip logic for dates with existing rates (DEFERRED - not in scope for MVP)

## 12. Refactor & Cleanup

- [x] 12.1 Run full test suite, verify all tests green (290 tests, 622 assertions)
- [x] 12.2 Run `vendor/bin/pint --dirty` to fix formatting
- [x] 12.3 Run `vendor/bin/phpstan analyse` (Larastan level 9 passes)
- [ ] 12.4 Update MIGRATION.md to mark Phase 2.1 as complete
