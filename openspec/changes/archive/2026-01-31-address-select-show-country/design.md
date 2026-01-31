## Context

Address selection dropdowns in UserProfiles and Entities forms currently display `street, city`. The Address model has a `country` field (ISO country code) but it is not shown in dropdowns, making it difficult to distinguish addresses across jurisdictions. Table views already display `city, country`, creating an inconsistency.

The display format is duplicated across 4 `<flux:select.option>` lines in two Blade views (entities and user-profiles), with no centralized formatting on the model.

## Goals / Non-Goals

**Goals:**
- Show country in address selection dropdowns across all forms
- Centralize address display formatting via a model accessor
- Maintain consistency between dropdown and table display formats

**Non-Goals:**
- Changing the address table list view format (already shows country)
- Adding country name resolution (ISO code → full name) — keep using the stored string
- Modifying the address CRUD form or validation

## Decisions

### Decision 1: Add `display_label` Attribute accessor on Address model

**Choice**: Eloquent attribute accessor returning `"{street}, {city} ({country})"`

**Rationale**: Centralizes the format in one place so all views (current and future) use the same display. The parenthetical format `(US)` distinguishes the country visually without cluttering the dropdown text.

**Alternatives considered**:
- Inline Blade changes only (no accessor): simpler but duplicates formatting logic across 4+ locations
- `__toString()` magic method: risks unintended side effects in serialization/logging; accessor is more explicit

### Decision 2: Parenthetical country format

**Choice**: `123 Main St, Miami (US)` instead of `123 Main St, Miami, US`

**Rationale**: The parentheses visually separate the country code from the city, making it scannable at a glance. This mirrors the table view where country is already a separate column.

## Risks / Trade-offs

- [Minimal risk] Slightly longer dropdown text — mitigated by the address dropdown being a full-width `<flux:select>` element with adequate space
- [Low risk] Existing test assertions that check address display text will need updating — mitigated by small, known set of affected tests
