## Context

MixIncome is adopting a model-by-model migration approach from Velor. The Jurisdiction model is the foundational building block that all other tax-related models will depend on (UserProfiles, Entities, TransactionCategories, TaxYears, etc.).

**Current state**: MixIncome has authentication working but no domain models beyond User.

**Constraints**:
- Must follow nunomaduro/essentials strictness (100% type coverage, immutability)
- Must use `declare(strict_types=1)` in all PHP files
- Must use `casts()` method instead of `$casts` property in models
- Must follow Laravel 12 conventions

**Stakeholders**: This sets the pattern for ~20 more model migrations from Velor.

## Goals / Non-Goals

**Goals:**
- Create a simple, strict Jurisdiction model with essential fields only
- Establish the migration pattern for future models (strict types, proper relationships)
- Seed initial data for Spain, USA, Colombia
- Provide admin UI for CRUD operations
- 100% test coverage (feature + unit tests)

**Non-Goals:**
- Tax year calendar fields (can be added later if needed)
- Filing types or tax forms (separate models)
- Complex business logic (this is a simple reference table)

## Decisions

### 1. Minimal Field Set

**Decision**: Only include essential fields: id, name, iso_code, timezone, default_currency, timestamps

**Rationale**: 
- Velor included `tax_year_start_month/day` but research shows they're rarely used
- We can extend the table later if tax reporting services actually need it
- Simpler is better for the foundational model

**Alternatives considered**:
- Include all Velor fields → Rejected: adds complexity without proven need
- Store timezone as UTC offset → Rejected: timezone identifiers are more robust

### 2. Model Structure

**Decision**: Follow nunomaduro/essentials strict pattern:
```php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Jurisdiction extends Model
{
    /** @var array<int, string> */
    protected $fillable = [...];
    
    public function userProfiles(): HasMany
    {
        return $this->hasMany(UserProfile::class);
    }
}
```

**Rationale**:
- Explicit return types satisfy type coverage requirements
- PHPDoc for array shapes improves static analysis
- Follows Laravel 12 + starter kit patterns

**Alternatives considered**:
- Use property type hints → Rejected: Laravel doesn't support typed properties for fillable yet
- Skip PHPDoc → Rejected: required for 100% type coverage

### 3. Seeder Approach

**Decision**: Create JurisdictionSeeder with upsert strategy (idempotent)

**Rationale**:
- Can be run multiple times safely
- Updates data if jurisdictions already exist
- Matches key on `iso_code` (natural key)

**Example**:
```php
Jurisdiction::upsert([
    ['iso_code' => 'ES', 'name' => 'Spain', 'timezone' => 'Europe/Madrid', 'default_currency' => 'EUR'],
    ['iso_code' => 'US', 'name' => 'United States', 'timezone' => 'America/New_York', 'default_currency' => 'USD'],
    ['iso_code' => 'CO', 'name' => 'Colombia', 'timezone' => 'America/Bogota', 'default_currency' => 'COP'],
], uniqueBy: ['iso_code'], update: ['name', 'timezone', 'default_currency']);
```

**Alternatives considered**:
- firstOrCreate in loop → Rejected: N queries instead of 1
- Skip seeder, only use factory → Rejected: production needs initial data

### 4. Admin UI Pattern

**Decision**: Create `Livewire/Management/Jurisdictions.php` component following Velor's Management pattern

**Structure**:
- Table view with all jurisdictions
- Inline editing (modal or slide-over)
- Delete with dependency check (soft validation: "Can't delete, has 3 entities")
- Uses Flux UI components (table, buttons, forms)

**Rationale**:
- Consistent with other "Management" screens we'll build
- Livewire keeps state on server (secure, simple)
- Flux UI provides polished components out of the box

**Alternatives considered**:
- API + Vue/React → Rejected: adds complexity, against project stack
- Blade only (no Livewire) → Rejected: poor UX for CRUD operations

### 5. Routes Structure

**Decision**: Create `routes/management.php` for all admin routes

**Rationale**:
- Separates admin routes from public/auth routes
- Easy to apply middleware (auth, admin role if needed later)
- Groups related functionality

**File structure**:
```php
// routes/management.php
Route::middleware(['auth', 'verified'])->prefix('management')->group(function () {
    Route::view('jurisdictions', 'management.jurisdictions')->name('management.jurisdictions');
    // Future: entities, currencies, etc.
});
```

**Alternatives considered**:
- Put in web.php → Rejected: will get cluttered with 20+ management routes
- Use /admin prefix → Rejected: /management is clearer (not role-based, just admin tools)

### 6. Testing Strategy

**Decision**: Feature tests for HTTP/Livewire interactions, unit tests for model logic

**Feature tests** (`tests/Feature/Management/JurisdictionTest.php`):
- Can view jurisdictions page
- Can create jurisdiction
- Can update jurisdiction
- Can delete jurisdiction (fails if has dependencies)
- Unique iso_code validation works

**Unit tests** (`tests/Unit/Models/JurisdictionTest.php`):
- Factory creates valid jurisdiction
- Relationships return correct types
- Fillable attributes work

**Rationale**:
- Feature tests ensure UI works end-to-end
- Unit tests are fast and test model contracts
- Matches Laravel/Pest best practices

## Risks / Trade-offs

**[Risk]** Future models may need tax_year_start fields  
**→ Mitigation**: Easy to add migration later. Starting simple is better than premature optimization.

**[Risk]** Timezone changes (e.g., USA has multiple timezones)  
**→ Mitigation**: Using America/New_York as default is reasonable. Can extend to per-Entity timezone later if needed.

**[Risk]** Deleting jurisdiction with dependencies could leave orphaned data  
**→ Mitigation**: Use foreign key constraints with `RESTRICT` in migrations. Livewire UI will show friendly error.

**[Risk]** 3 jurisdictions seems limiting  
**→ Mitigation**: Model is fully flexible. Admin UI allows adding more. 3 is MVP scope.

**[Trade-off]** Livewire overhead vs pure Blade  
**Accepted**: Better UX is worth the slight performance cost. Livewire caching mitigates most overhead.

## Migration Plan

1. Run migration: `php artisan migrate`
2. Run seeder: `php artisan db:seed --class=JurisdictionSeeder`
3. Verify data: `php artisan tinker` → `Jurisdiction::all()`
4. Access admin UI: `/management/jurisdictions`

**Rollback**: Migration has down() method. No data loss since this is a new table.

## Open Questions

None. This is a straightforward CRUD model with no complex business logic or external dependencies.
