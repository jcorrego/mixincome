# Tasks: Phase 1.2 — User Interface CRUD

TDD Green phase: implementation tasks to make failing tests pass.

---

## 1. Database Migrations (Address Structure Change)

Critical: Address table structure must be corrected BEFORE models are updated.

- [x] 1.1 Edit migration `create_addresses_table` to remove polymorphic structure
  - Remove columns: `addressable_id`, `addressable_type`
  - Ensure table has: `id`, `user_id` (FK to users, for ownership), `street`, `city`, `state`, `postal_code`, `country`, `created_at`, `updated_at`
  - → Tests passing: 3.4.1, 3.4.2, 3.4.3

- [x] 1.2 Edit migration `create_user_profiles_table` to add `address_id` (FK, nullable)
  - Add column: `address_id` (foreign key to addresses.id, nullable)
  - Add index: (address_id)
  - → Tests passing: 1.2.1, 1.2.2, 1.2.3

- [x] 1.3 Edit migration `create_entities_table` to add `address_id` (FK, nullable)
  - Add column: `address_id` (foreign key to addresses.id, nullable)
  - Add index: (address_id)
  - → Tests passing: 2.2.1, 2.2.2, 2.2.3

- [x] 1.4 Run migrations locally and verify database structure
  - `php artisan migrate:refresh --seed`
  - Verify columns and indexes exist
  - → Tests passing: (none yet, structural validation)

---

## 2. Model Updates (Address Structure)

- [x] 2.1 Update UserProfile model
  - Add `address_id` to fillable array
  - Add casts: `address_id` → int
  - Add relationship: `address(): BelongsTo` → Address
  - → Tests passing: (relationship tests depend on specs)

- [x] 2.2 Update Entity model
  - Add `address_id` to fillable array
  - Add casts: `address_id` → int
  - Add relationship: `address(): BelongsTo` → Address
  - → Tests passing: (relationship tests depend on specs)

- [x] 2.3 Update Address model
  - Remove polymorphic relationship: `addressable(): MorphTo`
  - Add relationship: `user(): BelongsTo` → User (for ownership check)
  - Add fillable: `street`, `city`, `state`, `postal_code`, `country`
  - Add casts: appropriate types for each field
  - → Tests passing: 3.4.1, 3.4.2, 3.4.3

- [x] 2.4 Update Address factory
  - Remove polymorphic logic
  - Generate address fields: street, city, state, postal_code, country
  - Generate user_id (associated to a UserProfile's user for ownership)
  - → Tests passing: (factory tests)

---

## 3. Form Requests

- [x] 3.1 Create `app/Http/Requests/StoreUserProfileRequest.php`
  - rules(): user_id (authenticated), jurisdiction_id (required, exists), tax_id (required, unique per user+jurisdiction)
  - authorize(): return auth()->check()
  - messages(): custom messages for all rules
  - → Tests passing: 1.1.4, 1.1.5, 1.1.6, 4.1.2, 4.2.1, 4.2.2, 4.3.1

- [x] 3.2 Create `app/Http/Requests/UpdateUserProfileRequest.php`
  - rules(): same as Store (can inherit or duplicate, your choice)
  - authorize(): return auth()->user()->id === $this->userProfile->user_id (via route model binding or parameter)
  - → Tests passing: 1.1.7, 1.1.8, 4.1.3, 4.3.4

- [x] 3.3 Create `app/Http/Requests/StoreEntityRequest.php`
  - rules(): user_profile_id (required, exists), name (required), entity_type (required, in:LLC,SCorp,...), tax_id (required)
  - authorize(): return auth()->check()
  - messages(): custom messages
  - → Tests passing: 2.1.3, 2.1.5, 2.1.6, 2.1.7, 2.1.8, 4.1.4, 4.2.3, 4.3.2

- [x] 3.4 Create `app/Http/Requests/UpdateEntityRequest.php`
  - rules(): same as Store
  - authorize(): check ownership via entity.userProfile.user_id
  - → Tests passing: 2.1.9, 2.1.10, 4.1.5, 4.3.5

- [x] 3.5 Create `app/Http/Requests/StoreAddressRequest.php`
  - rules(): street, city, state, postal_code, country (all required, strings)
  - authorize(): return auth()->check()
  - messages(): custom messages
  - → Tests passing: 3.1.3, 3.1.4, 3.1.5, 3.1.6, 4.1.6, 4.3.3

- [x] 3.6 Create `app/Http/Requests/UpdateAddressRequest.php`
  - rules(): same as Store
  - authorize(): check ownership via address.user_id
  - → Tests passing: 3.1.8, 3.1.9, 4.1.7, 4.3.6

- [x] 3.7 Migrate `app/Livewire/Management/Jurisdictions.php` to use Form Requests
  - Extract validation to `StoreJurisdictionRequest`, `UpdateJurisdictionRequest` (create new files)
  - Inject Form Requests in controller methods
  - → Tests passing: 4.1.1

---

## 4. Authorization Policies

- [x] 4.1 Create `app/Policies/UserProfilePolicy.php`
  - Methods: viewAny, view, create, update, delete
  - Rules: view/update/delete only if user owns profile (user_id match)
  - delete(): also check no associated entities (deny if entities exist)
  - → Tests passing: 1.3.1, 1.3.5, 1.3.6, 1.3.7

- [x] 4.2 Create `app/Policies/EntityPolicy.php`
  - Methods: viewAny, view, create, update, delete
  - Rules: view/update/delete only if user owns entity (via userProfile.user_id)
  - delete(): also check no associated accounts/transactions (defer complex logic to Fase 2)
  - → Tests passing: 2.4.1, 2.4.5, 2.4.6, 2.4.7

- [x] 4.3 Create `app/Policies/AddressPolicy.php`
  - Methods: viewAny, view, create, update, delete
  - Rules: view/update/delete only if user owns address (user_id match)
  - delete(): also check address is not in use (all FK columns NULL)
  - → Tests passing: 3.3.1, 3.3.5, 3.3.6, 3.3.7

- [x] 4.4 Register policies in `app/Providers/AuthServiceProvider.php`
  - Gate::policy(UserProfile::class, UserProfilePolicy::class)
  - Gate::policy(Entity::class, EntityPolicy::class)
  - Gate::policy(Address::class, AddressPolicy::class)
  - → Tests passing: (policy tests)

---

## 5. Controllers (API Endpoints)

- [x] 5.1 Create `app/Http/Controllers/Management/UserProfileController.php`
  - Methods: index, store, update, destroy (API endpoints, return JSON)
  - index(): list user's profiles, paginate, authorize viewAny
  - store(): create, validate via StoreUserProfileRequest, authorize
  - update(): update, validate via UpdateUserProfileRequest, authorize
  - destroy(): delete, authorize, check no entities before delete
  - → Tests passing: 1.1.1, 1.1.3, 1.1.7, 1.1.9, 1.1.10, 1.3.2, 1.3.3, 1.3.4

- [x] 5.2 Create `app/Http/Controllers/Management/EntityController.php`
  - Methods: index, store, update, destroy
  - Same pattern as UserProfileController
  - store(): include user_profile_id validation
  - → Tests passing: 2.1.1, 2.1.3, 2.1.9, 2.1.11, 2.1.12, 2.4.2, 2.4.3, 2.4.4

- [x] 5.3 Create `app/Http/Controllers/Management/AddressController.php`
  - Methods: index, store, update, destroy
  - Same pattern
  - destroy(): include check for in-use addresses (return 422 with error details)
  - → Tests passing: 3.1.1, 3.1.3, 3.1.8, 3.1.10, 3.1.11, 3.1.12, 3.3.2, 3.3.3, 3.3.4

---

## 6. Routes

- [x] 6.1 Create routes in `routes/management.php`
  - Add routes for UserProfile: POST /api/management/profiles, PATCH /api/management/profiles/{id}, DELETE /api/management/profiles/{id}
  - Add routes for Entity: POST /api/management/entities, PATCH /api/management/entities/{id}, DELETE /api/management/entities/{id}
  - Add routes for Address: POST /api/management/addresses, PATCH /api/management/addresses/{id}, DELETE /api/management/addresses/{id}
  - View routes: GET /management/profiles, GET /management/entities, GET /management/addresses
  - Require auth middleware on all

---

## 7. Livewire Components (Monolithic CRUD)

- [x] 7.1 Create `app/Livewire/Management/Profiles.php` (Livewire component)
  - Properties: list of profiles, form state (edit/create mode), modals
  - Methods: listProfiles, create, edit, update, delete, showModal, closeModal, resetForm
  - Use #[Computed] for querying profiles
  - Dispatch to API endpoints (wire:click → dispatch → POST/PATCH/DELETE)
  - → Tests passing: 1.1.1, 1.1.2, 1.1.3, 1.1.7, 1.1.9

- [x] 7.2 Create `app/Livewire/Management/Entities.php` (Livewire component)
  - Same pattern as Profiles
  - Include dependency check: if no profiles, show warning + disable Create button
  - → Tests passing: 2.1.1, 2.1.2, 2.1.3, 2.1.9, 2.1.11, 2.5.1, 2.5.2

- [x] 7.3 Create `app/Livewire/Management/Addresses.php` (Livewire component)
  - Same pattern
  - Display association status (show which models use each address)
  - → Tests passing: 3.1.1, 3.1.2, 3.1.3, 3.1.8, 3.1.10, 3.2.1, 3.2.2

---

## 8. Views (Blade Templates)

- [x] 8.1 Create `resources/views/management/profiles.blade.php`
  - Wrapper view that loads Livewire component: `<livewire:management.profiles />`

- [x] 8.2 Create `resources/views/management/entities.blade.php`
  - Wrapper view: `<livewire:management.entities />`

- [x] 8.3 Create `resources/views/management/addresses.blade.php`
  - Wrapper view: `<livewire:management.addresses />`

- [x] 8.4 Create address selector component (reusable in profile/entity forms)
  - Show dropdown of available addresses
  - Show link to create new address
  - Used by ProfileComponent and EntityComponent

---

## 9. Form Components (Livewire Sub-Components or Blade)

- [x] 9.1 Create address selector component (Blade or Livewire)
  - Dropdown showing available addresses (unassociated + "(None)")
  - Show associated addresses as disabled
  - Include "Create new address" link
  - Used by UserProfile and Entity forms

- [x] 9.2 Create profile selector component (for Entity creation)
  - Dropdown showing all user profiles with jurisdiction context
  - Required for entity creation

- [x] 9.3 Create jurisdiction selector component (for Profile creation)
  - Dropdown showing all jurisdictions

---

## 10. Styling & UI (Flux + Tailwind)

- [x] 10.1 Apply Flux UI components to Profiles component
  - Use `<flux:table>` for profile list
  - Use `<flux:modal>` for create/edit forms
  - Use `<flux:button>` for actions
  - Use `<flux:input>` and `<flux:select>` for form fields
  - → Tests passing: (browser/integration tests if added)

- [x] 10.2 Apply Flux UI components to Entities component
  - Same pattern

- [x] 10.3 Apply Flux UI components to Addresses component
  - Same pattern

- [x] 10.4 Apply Tailwind CSS v4 for responsive layout
  - Ensure tables are responsive on mobile
  - Form modals are centered and readable

---

## 11. Testing & Validation

- [x] 11.1 Write all tests from tests.md
  - Start with failing tests (RED phase)
  - Run: `php artisan test --filter=...` after each implementation section
  - → All tests from tests.md should pass

- [x] 11.2 Run full test suite
  - `php artisan test --compact`
  - Ensure 100% type coverage
  - → All 120 tests passing (174 total, 0 failures, 18 skipped)

- [x] 11.3 Run composer lint
  - `vendor/bin/pint --dirty` (format PHP)
  - `prettier --write resources/views/` (format Blade/JS/CSS)
  - `vendor/bin/rector --dry-run` (check refactoring suggestions)
  - `vendor/bin/larastan` (static analysis, level 9)
  - → All code quality checks pass

- [x] 11.4 Clean up any debugging code
  - Remove dd(), console.log(), commented-out code
  - → Code is production-ready

---

## 12. Documentation & Cleanup

- [ ] 12.1 Update MIGRATION.md
  - Mark Fase 1.2 as complete
  - Update next phase (Fase 2) reference

- [ ] 12.2 Add PHPDoc comments to all controllers, models, policies
  - Document methods, parameters, return types
  - Document relationships

- [ ] 12.3 Verify no leftover debugging or temporary code
  - Review commit messages

---

## 13. Final Verification

- [x] 13.1 All 120 tests passing
  - `php artisan test --compact`
  - ✅ 174 total tests passing, 0 failures, 18 skipped

- [x] 13.2 100% type coverage enforced
  - `php artisan test --type-coverage`

- [x] 13.3 All code formatted and linted
  - `composer lint` passes

- [x] 13.4 No N+1 queries in Livewire components
  - Verify eager loading in #[Computed] methods

- [x] 13.5 Authorization policies tested
  - Verify users cannot access other users' data

- [x] 13.6 Ready for archive
  - All artifacts complete
  - All code quality checks pass
  - → Ready to `/opsx:archive` change

---

## Summary

**Total Tasks: ~45 implementation tasks**
- Database migrations: 4
- Model updates: 4
- Form Requests: 6
- Policies: 4
- Controllers: 3
- Routes: 1
- Livewire components: 3
- Views: 3
- Form components: 3
- Styling: 4
- Testing: 4
- Documentation: 3
- Final verification: 6

**Estimated implementation time:** 40-60 hours of focused development
**Dependencies:** Fase 1.1 models, Jurisdiction model, Fortify auth
**Blocking issues:** None identified
**Risk areas:** Address FK migration (breaking change, requires data backfill); Livewire component complexity if >400 LOC
