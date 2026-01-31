## Why

Address selection dropdowns in UserProfiles and Entities forms currently display only `street, city`, while the table views show `city, country`. For a multi-jurisdiction tax platform spanning Spain, USA, and Colombia, omitting the country from selection lists makes it hard to distinguish between addresses in different countries (e.g., a Miami address vs. a Bogota address with similar street names).

## What Changes

- Update address display format in all `<flux:select.option>` dropdowns from `street, city` to `street, city, country` across UserProfiles and Entities forms (both create and edit)
- Add a `display_label` accessor on the Address model to centralize the display format and avoid inline Blade formatting duplication

## Capabilities

### New Capabilities

_(none)_

### Modified Capabilities

- `address-management`: Address display format changes — the model gains a `display_label` accessor for consistent formatting
- `entity-management`: Entity form address dropdown now includes country in the display text
- `user-profile-management`: UserProfile form address dropdown now includes country in the display text

## Impact

- **Views**: `resources/views/livewire/management/entities.blade.php`, `resources/views/livewire/management/user-profiles.blade.php` (4 `<flux:select.option>` lines)
- **Model**: `app/Models/Address.php` (new accessor)
- **Tests**: Existing Livewire component tests for UserProfiles and Entities need assertions updated for the new display format
