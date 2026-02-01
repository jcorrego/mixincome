## 1. Service Layer - FxRateService Extensions

- [ ] 1.1 Add fetchRateManual(Currency $from, Currency $to, CarbonInterface $date): FxRate method
- [ ] 1.2 Add duplicate check in fetchRateManual (throw FxRateException if rate exists)
- [ ] 1.3 Add ECB availability check in fetchRateManual (throw if weekend/holiday with no ECB data)
- [ ] 1.4 Add refetchRate(FxRate $rate): FxRate method
- [ ] 1.5 Implement ECB re-fetch logic with rate value update
- [ ] 1.6 Update is_replicated and replicated_from_date when ECB data becomes available
- [ ] 1.7 Handle unchanged rate values (update only updated_at timestamp)
      → Tests passing: 6.1, 6.2, 6.3, 7.1, 7.2, 7.3, 7.4, 7.5

## 2. Form Requests - Validation

- [ ] 2.1 Create FetchFxRateRequest in `app/Http/Requests/`
- [ ] 2.2 Add validation rules: from_currency_id (required, exists), to_currency_id (required, exists, different), date (required, date, not future)
- [ ] 2.3 Add custom error messages for validation failures
- [ ] 2.4 Create RefetchFxRateRequest in `app/Http/Requests/`
- [ ] 2.5 Add validation: rate_id (required, exists in fx_rates)
      → Tests passing: 3.3, 3.4, 3.5

## 3. Routes - Management URLs

- [ ] 3.1 Add auth middleware to `/management/*` routes in `routes/management.php`
- [ ] 3.2 Create GET `/management/currencies` → CurrencyIndex route
- [ ] 3.3 Create GET `/management/currencies/{currency}` → CurrencyShow route (Currency model binding)
- [ ] 3.4 Create POST `/management/currencies/{currency}/fetch-rate` → fetchRate action
- [ ] 3.5 Create POST `/management/currencies/{currency}/refetch-rate/{fxRate}` → refetchRate action (FxRate model binding)
      → Tests passing: 1.1, 1.2, 2.1

## 4. Livewire Components - CurrencyIndex

- [ ] 4.1 Create `app/Livewire/Management/CurrencyIndex.php` component
- [ ] 4.2 Add render() method fetching all currencies with Currency::all()
- [ ] 4.3 Create view at `resources/views/livewire/management/currency-index.blade.php`
- [ ] 4.4 Use Flux UI `<flux:table>` to display currencies (code, name, symbol, decimal_places)
- [ ] 4.5 Add "View Details" link to each currency row
      → Tests passing: 1.1, 1.2

## 5. Livewire Components - CurrencyShow

- [ ] 5.1 Create `app/Livewire/Management/CurrencyShow.php` component
- [ ] 5.2 Add public Currency $currency property (mount via route param)
- [ ] 5.3 Eager load sourceFxRates and targetFxRates relationships
- [ ] 5.4 Create view at `resources/views/livewire/management/currency-show.blade.php`
- [ ] 5.5 Display currency metadata (code, name, symbol, decimal_places)
- [ ] 5.6 Display "Rates From {code}" table with FX rates where currency is source
- [ ] 5.7 Display "Rates To {code}" table with FX rates where currency is target
- [ ] 5.8 Show rate metadata: date, rate value, source, is_replicated, replicated_from_date
      → Tests passing: 2.1, 2.2, 2.3, 2.4

## 6. Livewire Components - Fetch Rate Form

- [ ] 6.1 Add fetchRate(int $fromCurrencyId, int $toCurrencyId, string $date) action to CurrencyShow
- [ ] 6.2 Add form properties: public int $fromCurrencyId, public int $toCurrencyId, public string $date
- [ ] 6.3 Validate using FetchFxRateRequest rules
- [ ] 6.4 Call FxRateService::fetchRateManual() in try/catch
- [ ] 6.5 Display success notification with new rate details using Flux UI notification
- [ ] 6.6 Display error notification on FxRateException with retry option
- [ ] 6.7 Add wire:loading spinner to form submit button
- [ ] 6.8 Refresh rates list after successful fetch
      → Tests passing: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 5.1, 5.3

## 7. Livewire Components - Re-fetch Rate Action

- [ ] 7.1 Add refetchRate(int $fxRateId) action to CurrencyShow
- [ ] 7.2 Load FxRate by ID, call FxRateService::refetchRate($rate) in try/catch
- [ ] 7.3 Compare old and new rate values
- [ ] 7.4 Display success message: "Rate updated from {old} to {new}" or "Rate unchanged, ECB value matches"
- [ ] 7.5 Display error notification on FxRateException
- [ ] 7.6 Add wire:loading indicator to re-fetch button (per-row)
- [ ] 7.7 Refresh rates list after successful re-fetch
      → Tests passing: 4.1, 4.2, 4.3, 4.4, 5.2, 5.4

## 8. Views - Flux UI Forms

- [ ] 8.1 Create fetch rate form in CurrencyShow view with Flux UI components
- [ ] 8.2 Add `<flux:select>` for from_currency dropdown (all currencies)
- [ ] 8.3 Add `<flux:select>` for to_currency dropdown (all currencies)
- [ ] 8.4 Add `<flux:input type="date">` for date picker
- [ ] 8.5 Add `<flux:button wire:click="fetchRate">` with loading state
- [ ] 8.6 Add re-fetch button to each rate row: `<flux:button wire:click="refetchRate({{ $rate->id }})">` with loading state

## 9. Views - Rate Display Tables

- [ ] 9.1 Create "Rates From {currency}" table using `<flux:table>` in CurrencyShow
- [ ] 9.2 Add columns: To Currency, Date, Rate, Source, Replicated, Replicated From, Actions
- [ ] 9.3 Create "Rates To {currency}" table using `<flux:table>`
- [ ] 9.4 Add columns: From Currency, Date, Rate, Source, Replicated, Replicated From, Actions
- [ ] 9.5 Display is_replicated badge (Flux UI badge component)
- [ ] 9.6 Display replicated_from_date when is_replicated=true

## 10. Tests - Service Layer

- [ ] 10.1 Create tests/Unit/Services/FxRateServiceTest.php (if not exists, extend it)
- [ ] 10.2 Test fetchRateManual with valid date (mock ECB success)
- [ ] 10.3 Test fetchRateManual with duplicate rate (expect exception)
- [ ] 10.4 Test fetchRateManual with weekend date (expect exception)
- [ ] 10.5 Test refetchRate with different ECB value
- [ ] 10.6 Test refetchRate with same ECB value (timestamp updates)
- [ ] 10.7 Test refetchRate updating replicated rate to ECB-sourced
- [ ] 10.8 Test refetchRate when ECB still has no data (expect exception)
      → Tests: 6.1, 6.2, 6.3, 7.1, 7.2, 7.3, 7.4, 7.5

## 11. Tests - Feature (Routes & Auth)

- [ ] 11.1 Create tests/Feature/Management/CurrencyTest.php
- [ ] 11.2 Test GET /management/currencies as authenticated user (200, displays currencies)
- [ ] 11.3 Test GET /management/currencies as guest (redirect to login)
- [ ] 11.4 Test GET /management/currencies/{currency} as authenticated user (200, displays rates)
      → Tests: 1.1, 1.2, 2.1

## 12. Tests - Livewire Components

- [ ] 12.1 Create tests/Feature/Livewire/Management/CurrencyIndexTest.php
- [ ] 12.2 Test component renders with all currencies
- [ ] 12.3 Create tests/Feature/Livewire/Management/CurrencyShowTest.php
- [ ] 12.4 Test component renders with currency metadata and rates
- [ ] 12.5 Test fetchRate action with valid data (success)
- [ ] 12.6 Test fetchRate action with duplicate rate (validation error)
- [ ] 12.7 Test fetchRate action with future date (validation error)
- [ ] 12.8 Test fetchRate action with same currency (validation error)
- [ ] 12.9 Test fetchRate action with ECB failure (error message)
- [ ] 12.10 Test refetchRate action with different ECB value
- [ ] 12.11 Test refetchRate action with same ECB value
- [ ] 12.12 Test refetchRate action updating replicated rate
      → Tests: 2.2, 2.3, 2.4, 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 4.1, 4.2, 4.3, 4.4, 5.1, 5.2, 5.3, 5.4

## 13. Refactor & Cleanup

- [ ] 13.1 Run full test suite, verify all tests green (296 + 29 new = 325 expected)
- [ ] 13.2 Run `vendor/bin/pint --dirty` to fix formatting
- [ ] 13.3 Run `vendor/bin/phpstan analyse` (Larastan level 9 passes)
- [ ] 13.4 Verify 100% type coverage: `composer test`
- [ ] 13.5 Manual smoke test: Login, navigate to /management/currencies, fetch a rate, re-fetch a rate
- [ ] 13.6 Update MIGRATION.md to mark Phase 2.2 as complete
