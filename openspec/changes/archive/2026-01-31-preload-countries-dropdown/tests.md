## 1. Country Enum

- [x] 1.1 [Unit] Country enum contains expected cases like UnitedStates, Spain, Colombia (Scenario: Enum provides all recognized countries)
- [x] 1.2 [Unit] Country enum backing values are ISO alpha-2 codes — UnitedStates='US', Spain='ES', Colombia='CO' (Scenario: Enum provides all recognized countries)
- [x] 1.3 [Unit] Country::label() returns human-readable name — UnitedStates returns 'United States' (Scenario: Enum provides human-readable labels)
- [x] 1.4 [Unit] Country::options() returns array of value/label pairs sorted alphabetically by label (Scenario: Enum provides sorted options for dropdowns)
- [x] 1.5 [Unit] Country::options() with priority codes puts those countries first in given order (Scenario: Priority countries appear first)
- [x] 1.6 [Unit] Country enum can be used with Laravel's Enum validation rule (Scenario: Enum is usable as a Laravel validation rule)

## 2. Address Model Country Cast

- [x] 2.1 [Unit] Address model casts country attribute to Country enum (Scenario: Country attribute is cast to enum)
- [x] 2.2 [Unit] Address display_label accessor returns "{street}, {city} ({country_name})" format (Scenario: Display label includes country name)

## 3. Address CRUD with Country Dropdown

- [x] 3.1 [Feature] Address list displays country as full name from enum, not raw code (Scenario: List all user addresses)
- [x] 3.2 [Feature] Creating address with valid Country enum code succeeds (Scenario: Create a new address)
- [x] 3.3 [Feature] Creating address with invalid country code fails validation (Scenario: Create address fails on invalid country)
- [x] 3.4 [Feature] Creating address with missing required fields fails validation (Scenario: Create address fails on invalid data)
- [x] 3.5 [Feature] Editing address with valid Country enum code succeeds (Scenario: Edit an existing address)
- [x] 3.6 [Feature] Address form renders country as a select/dropdown, not text input (Scenario: Country field is a searchable dropdown)

## 4. Address Validation Rules

- [x] 4.1 [Unit] StoreAddressRequest validates country with Enum rule (Scenario: Store address validates country as enum)
- [x] 4.2 [Unit] UpdateAddressRequest validates country with Enum rule (Scenario: Update address validates country as enum)

## 5. Address Display in Related Forms

- [x] 5.1 [Feature] Entity form address dropdown displays addresses with display_label including country name (Scenario: Address dropdown shows display label with country)
- [x] 5.2 [Feature] UserProfile form address dropdown displays addresses with display_label including country name (Scenario: Address dropdown shows display label with country)
