## Why

The country field in address forms is currently a free-text input, which leads to inconsistent data (e.g., "US", "USA", "United States", "us"). Countries are a finite, well-known list — they should be preloaded in the system and presented as a searchable dropdown to ensure data consistency and improve UX.

## What Changes

- Add a `Country` PHP enum backed by ISO 3166-1 alpha-2 codes, covering the ~249 recognized countries/territories, with human-readable labels
- Replace the free-text `flux:input` for country with a searchable `flux:select` dropdown in address create/edit forms
- Cast the `country` column on the Address model to the new `Country` enum
- Update the Address factory to use the enum
- Update validation rules to accept only valid enum values
- Update address list table and dropdowns (entities, user-profiles) to display the country label instead of raw code

## Capabilities

### New Capabilities

- `country-enum`: A PHP backed enum providing ISO 3166-1 alpha-2 country codes with human-readable labels, usable across the application wherever country selection is needed

### Modified Capabilities

- `address-management`: The country field changes from free-text string to an enum-backed searchable dropdown. Validation changes from `required|string|max:255` to `required|enum:Country`. Display changes to show country name instead of raw ISO code.

## Impact

- **Models**: `Address` model gets a `Country` enum cast on the `country` attribute
- **Enums**: New `App\Enums\Country` enum
- **Views**: Address create/edit form (country input → searchable select), address list table (display label), entity/user-profile dropdowns (display label)
- **Validation**: `StoreAddressRequest` and `UpdateAddressRequest` rules updated
- **Factory**: `AddressFactory` updated to use enum
- **Tests**: Existing address tests updated, new tests for enum and dropdown behavior
- **No migration needed**: The `country` column already stores string values; ISO alpha-2 codes fit within the existing `string` column
