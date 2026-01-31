## Context

The Address model currently stores `country` as a free-text string field. Users type country names or codes manually, leading to inconsistent data (e.g., "US" vs "USA" vs "United States"). The existing `EntityType` enum in the project demonstrates the pattern for string-backed enums. The address form uses `flux:input` for all fields including country.

## Goals / Non-Goals

**Goals:**
- Provide a `Country` PHP string-backed enum with ISO 3166-1 alpha-2 codes and human-readable labels
- Replace free-text country input with a searchable `flux:select` dropdown
- Cast the `country` attribute on Address to the `Country` enum
- Update validation to accept only valid enum values
- Display country labels (not raw codes) in all address-related views

**Non-Goals:**
- No database migration — the existing `string` column accommodates ISO alpha-2 codes
- No localization of country names (English only for now)
- No country-flag icons or additional metadata
- No changes to the address data model or relationships

## Decisions

### Decision 1: PHP Enum vs Database Seed

**Chosen: PHP String-backed Enum**

Rationale: Countries are a stable, well-known list that rarely changes. A PHP enum provides:
- Compile-time type safety and autocomplete
- No database dependency or extra queries
- Laravel's built-in `Enum` validation rule
- Consistent with the existing `EntityType` enum pattern

Alternative considered: Database seeder with a `countries` table. Rejected because it adds query overhead for a static list, requires migration + seeder maintenance, and doesn't provide type safety at the PHP level.

### Decision 2: Enum Structure

The enum will use ISO 3166-1 alpha-2 codes as backing values (e.g., `US`, `ES`, `CO`) and a `label()` method returning the English name. Cases use TitleCase per project convention (e.g., `UnitedStates`, `Spain`, `Colombia`).

A static `options()` method will return an array suitable for Flux select dropdowns: `[['value' => 'US', 'label' => 'United States'], ...]`, sorted alphabetically by label.

### Decision 3: Searchable Dropdown Component

Use `flux:select` with its built-in search/filter capability. The dropdown will list all ~249 countries sorted alphabetically by label. The `searchable` attribute on `flux:select` enables client-side filtering.

### Decision 4: Display Label Accessor

Add a `display_label` attribute accessor to the Address model that returns `"{street}, {city} ({country_label})"` using the enum's label. This replaces inline formatting in entity/profile dropdown views.

### Decision 5: Backward Compatibility for Existing Data

Existing address records may have country values that don't match enum codes (e.g., "United States" instead of "US"). The enum cast will return `null` for unrecognized values. A data migration (artisan command or tinker script) can normalize existing data, but this is a single-user MVP, so manual correction is acceptable.

## Risks / Trade-offs

- **[Risk] Existing data mismatch** → Mitigated by the single-user MVP context; user can manually correct any mismatched records. If needed, a one-time artisan command can normalize data.
- **[Risk] Large enum file (~249 cases)** → Acceptable trade-off for type safety and no DB dependency. The file is generated once and rarely changes.
- **[Trade-off] English-only labels** → Sufficient for MVP. Localization can be added later by replacing `label()` with `__()` translations.
