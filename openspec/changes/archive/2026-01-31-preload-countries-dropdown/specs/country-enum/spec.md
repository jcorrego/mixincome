## ADDED Requirements

### Requirement: Country Enum with ISO 3166-1 Alpha-2 Codes

The system SHALL provide a `Country` PHP string-backed enum at `App\Enums\Country` containing all ISO 3166-1 alpha-2 country codes with human-readable English labels.

#### Scenario: Enum provides all recognized countries
- **WHEN** the `Country` enum is accessed
- **THEN** it SHALL contain cases for all ISO 3166-1 alpha-2 countries/territories (~249 entries)
- **AND** each case SHALL have a TitleCase name (e.g., `UnitedStates`, `Spain`, `Colombia`)
- **AND** each case SHALL be backed by the 2-letter ISO code string (e.g., `'US'`, `'ES'`, `'CO'`)

#### Scenario: Enum provides human-readable labels
- **WHEN** `Country::UnitedStates->label()` is called
- **THEN** it SHALL return `'United States'`
- **WHEN** `Country::Spain->label()` is called
- **THEN** it SHALL return `'Spain'`

#### Scenario: Enum provides sorted options for dropdowns
- **WHEN** `Country::options()` is called
- **THEN** it SHALL return an array of `['value' => string, 'label' => string]` pairs
- **AND** the array SHALL be sorted alphabetically by label
- **AND** each entry's `value` SHALL be the ISO alpha-2 code
- **AND** each entry's `label` SHALL be the English country name

#### Scenario: Enum is usable as a Laravel validation rule
- **WHEN** a form request uses `Rule::enum(Country::class)` or the `Enum:App\Enums\Country` validation rule
- **THEN** only valid ISO alpha-2 codes SHALL pass validation

---

### Requirement: Country Enum Prioritized Options

The system SHALL allow retrieving country options with specific countries prioritized at the top of the list, separated from the rest.

#### Scenario: Priority countries appear first
- **WHEN** `Country::options(['US', 'ES', 'CO'])` is called with priority codes
- **THEN** the returned array SHALL list US, ES, CO first (in the given order)
- **AND** then all remaining countries sorted alphabetically by label
