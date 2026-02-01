## Context

Phase 2.1 delivered the Currency and FxRate models with ECB API integration through EcbApiService and FxRateService. The system can fetch, replicate, and convert exchange rates, but all operations are code-driven. There's no user interface for administrators to:
- View available currencies and their exchange rates
- Manually fetch rates for specific dates (useful for weekends/holidays when ECB doesn't publish)
- Re-fetch existing rates if ECB data was updated or corrected
- Inspect rate metadata (source, replication status, dates)

This design adds a management UI at `/management/currencies` using Livewire for reactive interactions and Flux UI Free for components.

**Current State:**
- Currency model: 3 currencies (USD, EUR, COP) seeded
- FxRate model: Stores historical rates with source tracking
- EcbApiService: Fetches rates from ECB SDMX API
- FxRateService: findRate, fetchRate, replicateRate, convert methods
- Auth: Fortify authentication in place (Phase 0)

**Constraints:**
- Must use Livewire v4 + Volt for UI (project standard)
- Must use Flux UI Free components for forms/tables
- Admin features require authentication (restrict to logged-in users)
- All service calls must handle exceptions and provide user feedback
- ECB API has rate limits (retry logic already implemented in EcbApiService)

## Goals / Non-Goals

**Goals:**
- Display all currencies with metadata (code, name, symbol, decimal places)
- Show FX rates associated with each currency (both as source and target)
- Enable manual fetching of new rates for a specific date via form
- Enable re-fetching/overwriting existing rates from ECB
- Display rate metadata: date, rate value, source, is_replicated flag, replicated_from_date
- Provide real-time feedback during async operations (Livewire loading states)
- Restrict access to authenticated users only

**Non-Goals:**
- Editing currency metadata (currencies are hard-coded, no CRUD)
- Bulk operations (fetch multiple dates at once - future enhancement)
- Rate history charts/visualizations (future enhancement)
- Deleting rates manually (rates are historical records, shouldn't be deleted)
- Cross-rate or inverse rate calculations (deferred from Phase 2.1)
- Public API endpoints (this is admin UI only)

## Decisions

### 1. UI Technology: Livewire v4 with Volt single-file components

**Decision:** Use Livewire for CurrencyIndex and CurrencyShow pages with Volt functional API.

**Rationale:**
- Project standard (Phase 0, 1.2 established pattern)
- Real-time feedback without writing JavaScript
- Volt reduces boilerplate for simple components
- Flux UI Free integrates seamlessly with Livewire

**Alternatives Considered:**
- Plain Blade with form submissions: No real-time feedback, page reloads
- Inertia.js: Adds complexity, not needed for admin UI
- API + Vue/React: Overkill for internal admin panel

### 2. Route Structure: `/management/currencies` prefix

**Decision:** 
- `GET /management/currencies` → CurrencyIndex (list all)
- `GET /management/currencies/{currency}` → CurrencyShow (detail + rates)
- `POST /management/currencies/{currency}/fetch-rate` → Fetch new rate action
- `POST /management/currencies/{currency}/refetch-rate/{fxRate}` → Re-fetch existing rate

**Rationale:**
- Consistent with future management routes (transactions, entities, etc.)
- RESTful resource structure
- Currency model binding for type safety
- Separate actions for fetch (new) vs refetch (overwrite)

**Alternatives Considered:**
- `/admin/currencies`: Less semantic than "management"
- Single `/fetch-rate` action: Harder to distinguish new vs overwrite intent

### 3. Authorization: Middleware-based, user must be authenticated

**Decision:** Apply `auth` middleware to all `/management/*` routes.

**Rationale:**
- Simple and sufficient for MVP (single-user system)
- Fortify handles authentication (Phase 0)
- Future-proof: Can add role-based policies later (admin vs viewer)

**Alternatives Considered:**
- Policy-based authorization: Overkill for single-user MVP
- No auth: Security risk, inappropriate for admin features

### 4. Service Layer Extension: Add public methods to FxRateService

**Decision:** Add `fetchRateManual(Currency $from, Currency $to, CarbonInterface $date): FxRate` and `refetchRate(FxRate $rate): FxRate` to FxRateService.

**Rationale:**
- Keeps business logic in service layer, not controllers/Livewire
- `fetchRateManual`: Explicitly for admin UI, always creates new rate (throws if exists)
- `refetchRate`: Re-calls ECB API and updates existing FxRate record
- Both methods throw FxRateException on failure (ECB API errors, validation)

**Alternatives Considered:**
- Reuse existing `fetchRate()`: Ambiguous intent (does it overwrite?), violates single responsibility
- New service (FxRateAdminService): Unnecessary abstraction for 2 methods

### 5. Form Validation: Form Request classes

**Decision:** Create `FetchFxRateRequest` and `RefetchFxRateRequest` for validation.

**Rationale:**
- Project standard (Phase 0, 1.2)
- Centralizes validation rules and error messages
- Type-safe in controllers/Livewire actions

**Validation Rules:**
- `from_currency_id`: required, exists in currencies table
- `to_currency_id`: required, exists in currencies table, different from from_currency_id
- `date`: required, date format, not future date
- Refetch: rate_id required, exists in fx_rates table

### 6. UI Components: Flux UI Free for tables and forms

**Decision:** Use `<flux:table>`, `<flux:input>`, `<flux:button>`, `<flux:modal>` components.

**Rationale:**
- Project standard (Phase 1.2 established Flux UI usage)
- Consistent styling with rest of application
- Accessibility built-in
- Livewire-compatible

**Alternatives Considered:**
- Custom HTML + Tailwind: More work, inconsistent with existing UI
- Blade components library: Flux is already installed and preferred

## Risks / Trade-offs

**[Risk]** ECB API failures during manual fetch → **Mitigation:** EcbApiService has retry logic (3 attempts with exponential backoff). Display user-friendly error messages on failure. User can retry.

**[Risk]** User fetches rate for date with existing rate → **Mitigation:** `fetchRateManual()` throws exception if rate exists. UI shows error message: "Rate already exists. Use 'Re-fetch' to overwrite."

**[Risk]** Concurrent requests (multiple tabs) trying to fetch same rate → **Mitigation:** FxRateService uses `firstOrCreate()` for database atomicity (Phase 2.1 fix). Last write wins.

**[Risk]** User fetches rate for unsupported currency pair (e.g., COP→EUR direct) → **Mitigation:** ECB only provides EUR-based rates. Service throws FxRateException with clear message. Future: cross-rate calculations (deferred from Phase 2.1).

**[Trade-off]** No bulk operations (fetch multiple dates) → **Benefit:** Simpler UI and validation. **Cost:** Manual one-by-one fetching for historical gaps. **Decision:** Acceptable for MVP; bulk feature can be added later.

**[Trade-off]** No rate deletion → **Benefit:** Preserves historical accuracy. **Cost:** Can't undo incorrect manual entries. **Decision:** Re-fetch can correct errors; deletion is too risky for historical data.

**[Trade-off]** No audit log for manual actions → **Benefit:** Simpler implementation. **Cost:** Can't track who fetched/re-fetched rates. **Decision:** Acceptable for single-user MVP. Future: add user_id and action tracking to fx_rates.

## Migration Plan

**Deployment Steps:**
1. Run database migration: Not needed (no schema changes)
2. Deploy code: Routes, Livewire components, views, service methods
3. Smoke test: Login, navigate to `/management/currencies`, verify currency list loads
4. Functional test: Fetch a new rate for a weekend date, verify it appears in currency detail
5. Edge case test: Try fetching duplicate rate, verify error handling

**Rollback Strategy:**
- Remove routes from `routes/management.php`
- Service methods are additive (non-breaking); can remain without UI
- No database changes to rollback

**Zero-Downtime:** Yes (additive changes only, no breaking modifications)

## Open Questions

None. All technical decisions are clear based on existing Phase 2.1 foundation and project conventions.
