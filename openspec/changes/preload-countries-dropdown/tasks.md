## 1. Country Enum

- [x] 1.1 Create `App\Enums\Country` string-backed enum with all ISO 3166-1 alpha-2 codes, TitleCase case names, and `label()` method returning English country name
- [x] 1.2 Add `options(array $priority = [])` static method that returns `[['value' => string, 'label' => string], ...]` sorted alphabetically, with priority countries first
- [x] 1.3 Write failing tests for Country enum (tests 1.1–1.6) in `tests/Unit/Enums/CountryTest.php`
- [x] 1.4 Run tests, confirm green for group 1
      → Tests passing: 1.1, 1.2, 1.3, 1.4, 1.5, 1.6

## 2. Address Model Updates

- [x] 2.1 Update Address model: cast `country` to `Country` enum in `casts()` method
- [x] 2.2 Add `display_label` attribute accessor to Address model: `"{street}, {city} ({country_label})"`
- [x] 2.3 Update Address model PHPDoc `@property` for `country` to reflect `Country` type
- [x] 2.4 Update `AddressFactory` to use `Country` enum (e.g., `fake()->randomElement(Country::cases())`)
- [x] 2.5 Write failing tests for model cast and accessor (tests 2.1–2.2) in `tests/Unit/Models/AddressTest.php`
- [x] 2.6 Run tests, confirm green for group 2
      → Tests passing: 2.1, 2.2

## 3. Validation Rules Update

- [x] 3.1 Update `StoreAddressRequest`: change country rule from `['required', 'string', 'max:255']` to `['required', Rule::enum(Country::class)]`
- [x] 3.2 Update `UpdateAddressRequest`: same country rule change
- [x] 3.3 Write failing tests for validation rules (tests 4.1–4.2)
- [x] 3.4 Run tests, confirm green for group 4
      → Tests passing: 4.1, 4.2

## 4. Address CRUD Livewire Component

- [ ] 4.1 Update `Addresses` Livewire component: add `Country` enum import, expose `Country::options(['US', 'ES', 'CO'])` for the view
- [ ] 4.2 Update address create/edit form: replace `flux:input` for country with searchable `flux:select` populated from Country enum options
- [ ] 4.3 Update address list table: display `$address->country->label()` instead of raw code
- [ ] 4.4 Write/update feature tests for address CRUD with enum (tests 3.1–3.6)
- [ ] 4.5 Run tests, confirm green for group 3
      → Tests passing: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6

## 5. Address Display in Entity/Profile Forms

- [ ] 5.1 Update entity form: replace inline `{{ $address->street }}, {{ $address->city }}` with `{{ $address->display_label }}`
- [ ] 5.2 Update user-profile form: same display_label replacement
- [ ] 5.3 Write/update feature tests for entity and profile dropdown display (tests 5.1–5.2)
- [ ] 5.4 Run tests, confirm green for group 5
      → Tests passing: 5.1, 5.2

## 6. Existing Tests & Quality

- [ ] 6.1 Update existing `AddressTest.php` feature tests to use valid Country enum codes instead of free-text country values
- [ ] 6.2 Run full address test suite: `php artisan test --compact tests/Feature/Management/AddressTest.php`
- [ ] 6.3 Run `vendor/bin/pint --dirty` to fix formatting
- [ ] 6.4 Run `composer lint` for full quality check (Pint + Rector + Larastan)
- [ ] 6.5 Verify all tests pass: `php artisan test --compact`
