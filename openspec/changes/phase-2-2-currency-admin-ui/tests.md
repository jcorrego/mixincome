## Test Plan

This document maps requirements from the specs to test cases.

### Capability: currency-admin-ui

#### Test Group: Display Currency List

**Test 1.1: View currency index page**
- Setup: Seed Currency table with USD, EUR, COP
- Action: GET /management/currencies as authenticated user
- Assert: Status 200, page displays 3 currencies with code, name, symbol, decimal_places

**Test 1.2: Unauthenticated access denied**
- Setup: None (no auth)
- Action: GET /management/currencies as guest
- Assert: Redirect to login page (302)

#### Test Group: Display Currency Detail

**Test 2.1: View currency detail page**
- Setup: Seed Currency (USD, EUR, COP) and 5 FxRate records (EUR→USD, EUR→COP, COP→USD)
- Action: GET /management/currencies/eur as authenticated user
- Assert: Status 200, displays EUR metadata and related rates

**Test 2.2: Display rates as source currency**
- Setup: Seed EUR→USD and EUR→COP rates
- Action: GET /management/currencies/eur
- Assert: Page shows EUR→USD and EUR→COP in "From EUR" section

**Test 2.3: Display rates as target currency**
- Setup: Seed EUR→USD and COP→USD rates
- Action: GET /management/currencies/usd
- Assert: Page shows EUR→USD and COP→USD in "To USD" section

**Test 2.4: Display rate metadata**
- Setup: Seed replicated rate (is_replicated=true, replicated_from_date set) and ECB rate (is_replicated=false)
- Action: GET /management/currencies/eur
- Assert: Each rate shows date, rate value, source (ecb), is_replicated flag, replicated_from_date when applicable

#### Test Group: Fetch New Rate

**Test 3.1: Fetch rate for valid date**
- Setup: Mock EcbApiService to return rate 1.08, no existing rate for 2024-06-14
- Action: Submit fetch form with from=EUR, to=USD, date=2024-06-14
- Assert: FxRate created with rate=1.08, source='ecb', is_replicated=false, success message displayed

**Test 3.2: Fetch rate for weekend date**
- Setup: Mock EcbApiService to throw "no data" exception, existing rate for 2024-06-14 (Friday)
- Action: Submit fetch form for 2024-06-15 (Saturday)
- Assert: FxRate created with replicated rate from Friday, is_replicated=true, replicated_from_date=2024-06-14

**Test 3.3: Duplicate rate validation**
- Setup: Existing FxRate for EUR→USD on 2024-06-14
- Action: Submit fetch form with from=EUR, to=USD, date=2024-06-14
- Assert: Validation error: "Rate already exists. Use 'Re-fetch' to overwrite."

**Test 3.4: Future date validation**
- Setup: None
- Action: Submit fetch form with date = tomorrow
- Assert: Validation error: "Date cannot be in the future."

**Test 3.5: Same currency validation**
- Setup: None
- Action: Submit fetch form with from=USD, to=USD
- Assert: Validation error: "From and to currencies must be different."

**Test 3.6: ECB API failure handling**
- Setup: Mock EcbApiService to throw exception after retries exhausted
- Action: Submit fetch form
- Assert: Error message displayed with retry option, no FxRate created

#### Test Group: Re-fetch Existing Rate

**Test 4.1: Re-fetch rate successfully**
- Setup: Existing FxRate (EUR→USD, rate=1.08, date=2024-06-14), mock ECB returns 1.09
- Action: Click "Re-fetch" button on that rate
- Assert: FxRate updated to rate=1.09, updated_at timestamp changed, success message displayed

**Test 4.2: Re-fetch with different ECB value**
- Setup: Existing FxRate (rate=1.08), mock ECB returns 1.09
- Action: Click "Re-fetch"
- Assert: Display message: "Rate updated from 1.08 to 1.09"

**Test 4.3: Re-fetch with same ECB value**
- Setup: Existing FxRate (rate=1.08), mock ECB returns 1.08
- Action: Click "Re-fetch"
- Assert: Display message: "Rate unchanged, ECB value matches existing rate", updated_at still updated

**Test 4.4: Re-fetch replicated rate**
- Setup: Replicated FxRate (is_replicated=true, replicated_from_date=2024-06-14), mock ECB now has rate 1.08
- Action: Click "Re-fetch"
- Assert: FxRate updated with is_replicated=false, replicated_from_date=null, rate=1.08, success message

#### Test Group: Realtime Feedback

**Test 5.1: Loading state during fetch**
- Setup: None
- Action: Submit fetch form (Livewire test)
- Assert: Loading spinner visible, form disabled until Livewire action completes

**Test 5.2: Loading state during re-fetch**
- Setup: Existing FxRate
- Action: Click re-fetch button (Livewire test)
- Assert: Loading indicator on specific rate row, button disabled until action completes

**Test 5.3: Success notification**
- Setup: Mock successful fetch
- Action: Submit fetch form
- Assert: Success message appears with rate details, auto-dismisses after 5 seconds

**Test 5.4: Error notification**
- Setup: Mock ECB failure
- Action: Submit fetch form
- Assert: Error message appears with reason and suggested action (retry)

### Capability: fx-rate-service

#### Test Group: Manual Rate Fetching

**Test 6.1: Fetch new rate manually**
- Setup: Mock EcbApiService to return rate 1.08, no existing rate
- Action: Call FxRateService::fetchRateManual(USD, EUR, 2024-06-14)
- Assert: Returns new FxRate with rate=1.08, source='ecb', is_replicated=false

**Test 6.2: Prevent duplicate manual fetch**
- Setup: Existing FxRate for USD→EUR on 2024-06-14
- Action: Call fetchRateManual(USD, EUR, 2024-06-14)
- Assert: Throws FxRateException: "Rate already exists for this currency pair and date"

**Test 6.3: Manual fetch for replicated rate**
- Setup: Mock EcbApiService to throw "no data" for weekend
- Action: Call fetchRateManual(USD, EUR, 2024-06-15 Saturday)
- Assert: Throws FxRateException: "ECB has no rate for this date. Rate would be replicated."

#### Test Group: Rate Re-fetching

**Test 7.1: Re-fetch existing rate**
- Setup: Existing FxRate (rate=1.08), mock ECB returns 1.09
- Action: Call FxRateService::refetchRate($existingRate)
- Assert: Returns updated FxRate with rate=1.09, updated_at changed

**Test 7.2: Re-fetch updates rate value**
- Setup: Existing FxRate (rate=1.08), mock ECB returns 1.10
- Action: Call refetchRate($rate)
- Assert: FxRate.rate = 1.10, FxRate.updated_at > previous timestamp

**Test 7.3: Re-fetch updates replication status**
- Setup: Replicated FxRate (is_replicated=true, replicated_from_date=2024-06-14), mock ECB now has rate 1.08
- Action: Call refetchRate($rate)
- Assert: is_replicated=false, replicated_from_date=null, rate=1.08

**Test 7.4: Re-fetch when ECB still has no data**
- Setup: FxRate for weekend date, mock ECB throws "no data"
- Action: Call refetchRate($rate)
- Assert: Throws FxRateException: "ECB has no rate for this date"

**Test 7.5: Re-fetch returns same value**
- Setup: Existing FxRate (rate=1.08, updated_at old timestamp), mock ECB returns 1.08
- Action: Call refetchRate($rate)
- Assert: rate unchanged (1.08), updated_at timestamp is newer

## Test Coverage Requirements

- **Unit Tests:** FxRateService methods (fetchRateManual, refetchRate) - 5 tests
- **Feature Tests:** Currency admin routes and authorization - 2 tests
- **Livewire Component Tests:** CurrencyIndex, CurrencyShow interactions - 10 tests
- **Integration Tests:** End-to-end fetch/refetch with mocked ECB API - 7 tests
- **Edge Case Tests:** Validation, error handling, replicated rates - 5 tests

**Minimum Expected:** 29 new tests
**Target Code Coverage:** 100% of new code (service methods, Livewire components, form requests)

## Test Execution Strategy

1. Run unit tests first: `php artisan test --filter=FxRateServiceTest`
2. Run feature tests: `php artisan test tests/Feature/Management/CurrencyTest.php`
3. Run Livewire tests: `php artisan test tests/Feature/Livewire/Management/`
4. Run full suite: `php artisan test --compact`
5. Verify type coverage: `composer test` (includes type-coverage plugin)

## Mock Strategy

- **EcbApiService:** Mock for all tests (avoid real HTTP calls)
- **FxRateService:** Test directly for service tests, mock for Livewire tests
- **Database:** Use factories and seeders, in-memory SQLite for speed
- **Time:** Use Carbon::setTestNow() for consistent date testing

## Assertion Patterns

- **Database assertions:** `assertDatabaseHas`, `assertDatabaseMissing`, `assertDatabaseCount`
- **Livewire assertions:** `assertSet`, `assertSee`, `assertEmitted`, `assertDispatched`
- **Exception assertions:** `expect()->toThrow()`, verify exception message
- **Response assertions:** `assertOk`, `assertRedirect`, `assertSee`, `assertStatus`
