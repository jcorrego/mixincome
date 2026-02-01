## Why

Phase 2.1 implemented the core Currency and FxRate models with ECB API integration, but there's no admin interface to manage these entities. Users need a way to view currencies, inspect historical exchange rates, manually fetch rates for specific dates (weekends/holidays), and re-fetch rates from ECB if data needs to be updated or corrected.

## What Changes

- Create a Currency Admin UI accessible at `/management/currencies`
- Display a list of all currencies (USD, EUR, COP) with their metadata
- For each currency, show associated exchange rates (as source or target)
- Provide action to fetch a new exchange rate for a specific date using EcbApiService
- Provide action to re-fetch/overwrite an existing rate from ECB
- Show whether rates are ECB-sourced or replicated (is_replicated flag)
- Add authorization to restrict access to authenticated users only
- Include Livewire components for real-time feedback during API calls

## Capabilities

### New Capabilities
- `currency-admin-ui`: Admin interface for viewing currencies, managing exchange rates, fetching new rates by date, and re-fetching existing rates from ECB

### Modified Capabilities
- `fx-rate-service`: Add public methods for manual rate fetching/overwriting to support admin UI actions

## Impact

**New Files:**
- Routes: `routes/management.php` (add currency admin routes)
- Livewire Components: `app/Livewire/Management/CurrencyIndex.php`, `app/Livewire/Management/CurrencyShow.php`
- Views: `resources/views/livewire/management/currency-index.blade.php`, `resources/views/livewire/management/currency-show.blade.php`
- Form Requests: `app/Http/Requests/FetchFxRateRequest.php`
- Tests: Feature tests for routes, Livewire component tests

**Modified Files:**
- `app/Services/FxRateService.php`: Add methods for manual admin actions (fetch by date, overwrite existing)

**Dependencies:**
- Existing: Currency model, FxRate model, EcbApiService, FxRateService (Phase 2.1)
- Authorization via Laravel's built-in auth (Fortify from Phase 0)
- Flux UI Free components for forms and tables
- Livewire v4 for reactive UI
