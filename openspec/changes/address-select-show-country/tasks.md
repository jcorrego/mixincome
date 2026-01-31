## 1. TDD Red Phase — Write Failing Tests

- [x] 1.1 Create unit test for Address `display_label` accessor in `tests/Unit/Models/AddressTest.php`
      → Tests: 1.1, 1.2 (must fail — accessor doesn't exist yet)
- [x] 1.2 Add/update feature tests for Entity Livewire component to assert address dropdown shows "street, city (country)" format in `tests/Feature/Livewire/Management/EntitiesTest.php`
      → Tests: 2.1, 2.2 (must fail — views still use old format)
- [x] 1.3 Add/update feature tests for UserProfiles Livewire component to assert address dropdown shows "street, city (country)" format in `tests/Feature/Livewire/Management/UserProfilesTest.php`
      → Tests: 3.1, 3.2 (must fail — views still use old format)
- [x] 1.4 Run all new tests, confirm they all FAIL (red phase verified)

## 2. TDD Green Phase — Implementation

- [x] 2.1 Add `display_label` attribute accessor to `app/Models/Address.php` returning `"{street}, {city} ({country})"`
      → Tests passing: 1.1, 1.2
- [ ] 2.2 Update `resources/views/livewire/management/entities.blade.php` — replace inline `{{ $address->street }}, {{ $address->city }}` with `{{ $address->display_label }}` in both create and edit form dropdowns
      → Tests passing: 2.1, 2.2
- [ ] 2.3 Update `resources/views/livewire/management/user-profiles.blade.php` — replace inline `{{ $address->street }}, {{ $address->city }}` with `{{ $address->display_label }}` in both create and edit form dropdowns
      → Tests passing: 3.1, 3.2

## 3. Verify & Cleanup

- [ ] 3.1 Run all affected tests, confirm all GREEN
- [ ] 3.2 Run full test suite (`php artisan test --compact`) to check for regressions
- [ ] 3.3 Run `vendor/bin/pint --dirty` for code formatting
- [ ] 3.4 Run PHPStan to verify type coverage (`vendor/bin/phpstan analyse`)
