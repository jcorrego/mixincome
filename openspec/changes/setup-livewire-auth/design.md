# Design: setup-livewire-auth

## Overview

Bootstrap MixIncome's UI and authentication stack by installing Livewire v4, Volt v1, Flux UI Free v2, Fortify v1, and Sanctum v4 — then wiring up all auth flows, layouts, and settings pages. This mirrors the working Velor architecture but adapts to MixIncome's stricter codebase (PHP 8.4, 100% type coverage, nunomaduro/essentials).

## Architecture Decisions

### AD-1: Fortify for Headless Auth (not Breeze/Jetstream)
**Decision:** Use Laravel Fortify directly with custom Livewire views.
**Rationale:** Fortify gives us full control over the UI while handling all auth logic (login, register, reset, verify, 2FA). Breeze/Jetstream would overwrite our starter kit's strict setup. This is proven in Velor.

### AD-2: Livewire + Volt + Flux UI as UI Stack
**Decision:** All interactive UI via Livewire v4 components, Volt single-file components where appropriate, styled with Flux UI Free v2.
**Rationale:** Server-driven reactivity matches our "state on server, UI reflects it" convention. Flux UI provides consistent accessible components. No JS framework needed.

### AD-3: FortifyServiceProvider Pattern
**Decision:** Dedicated `FortifyServiceProvider` registered in `bootstrap/providers.php`, handling view bindings, actions, and rate limiting.
**Rationale:** Isolates auth configuration. Follows Velor's proven pattern. All Fortify views point to Livewire Blade views.

### AD-4: Strict Type Adaptation
**Decision:** All new PHP files use `declare(strict_types=1)`, PHP 8.4 features (constructor promotion, readonly where appropriate), full type hints, and PHPDoc blocks.
**Rationale:** MixIncome enforces 100% type coverage via Pest plugin. Every file must pass Larastan level 9.

## Package Installation Plan

### Composer Packages (require)
```
laravel/fortify:^1.30
laravel/sanctum:^4.2
livewire/livewire:^4.0
livewire/volt:^1.10
livewire/flux:^2.9
```

### Post-Install Steps
1. `php artisan vendor:publish --provider="Laravel\Fortify\FortifyServiceProvider"` → config + migrations
2. `php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"` → config + migrations
3. `php artisan livewire:publish --config`
4. `php artisan volt:install`
5. `php artisan flux:install` (Flux UI setup)
6. `php artisan migrate`

## File Structure

```
app/
├── Actions/Fortify/
│   ├── CreateNewUser.php          # Registration logic
│   └── ResetUserPassword.php      # Password reset logic
├── Livewire/
│   ├── Actions/
│   │   └── Logout.php             # Logout action class
│   └── Settings/
│       ├── Appearance.php         # Dark mode toggle
│       ├── DeleteUserForm.php     # Account deletion
│       ├── Password.php           # Password change
│       ├── Profile.php            # Profile editing
│       └── TwoFactor.php          # 2FA management
├── Models/
│   └── User.php                   # + TwoFactorAuthenticatable, HasApiTokens
└── Providers/
    └── FortifyServiceProvider.php # Auth config

resources/views/
├── layouts/
│   ├── app.blade.php              # Authenticated layout (sidebar, user menu)
│   └── auth.blade.php             # Guest layout (centered form, logo)
├── livewire/
│   ├── auth/
│   │   ├── login.blade.php
│   │   ├── register.blade.php
│   │   ├── forgot-password.blade.php
│   │   ├── reset-password.blade.php
│   │   ├── verify-email.blade.php
│   │   ├── confirm-password.blade.php
│   │   └── two-factor-challenge.blade.php
│   └── settings/
│       ├── appearance.blade.php
│       ├── delete-user-form.blade.php
│       ├── password.blade.php
│       ├── profile.blade.php
│       └── two-factor.blade.php
├── partials/
│   ├── head.blade.php             # Shared <head> (Vite, Livewire styles)
│   └── settings-heading.blade.php # Settings nav tabs
├── components/
│   ├── app-logo.blade.php         # MixIncome logo
│   ├── app-logo-icon.blade.php    # MixIncome icon
│   ├── auth-header.blade.php      # Auth page header
│   └── desktop-user-menu.blade.php
├── dashboard.blade.php
└── settings/                      # Settings route views
    ├── profile.blade.php
    ├── password.blade.php
    └── appearance.blade.php

routes/
├── web.php                        # Main routes (dashboard, auth redirects)
└── settings.php                   # Settings routes group

config/
├── fortify.php                    # Fortify features config
└── sanctum.php                    # Sanctum config

database/migrations/
├── xxxx_add_two_factor_columns_to_users_table.php
├── xxxx_create_personal_access_tokens_table.php
└── xxxx_create_sessions_table.php  # If not exists
```

## Key Implementation Details

### User Model Changes
```php
final class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, TwoFactorAuthenticatable, HasApiTokens;
    // Add two_factor_* and related columns to casts
}
```

### FortifyServiceProvider
- View bindings: each auth view points to `livewire.auth.*`
- Actions: `CreateNewUser`, `ResetUserPassword`
- Rate limiting: 5/min for login (email+IP key), 5/min for 2FA (session key)
- Features: registration, resetPasswords, emailVerification, twoFactorAuthentication

### Route Structure
```php
// web.php
Route::get('/', fn() => auth()->check() ? redirect()->route('dashboard') : view('livewire.auth.login'));
Route::view('dashboard', 'dashboard')->middleware(['auth', 'verified'])->name('dashboard');
require __DIR__.'/settings.php';

// settings.php
Route::middleware(['auth', 'verified'])->prefix('settings')->group(function () {
    Route::redirect('/', 'settings/profile');
    Route::view('profile', 'settings.profile')->name('settings.profile');
    Route::view('password', 'settings.password')->name('settings.password');
    Route::view('appearance', 'settings.appearance')->name('settings.appearance');
});
```

### Layouts
- **App layout:** Flux UI `<flux:sidebar>` with nav items, `<flux:header>` with user menu, `<flux:main>` for content slot
- **Auth layout:** Centered card with logo, form slot, minimal chrome

### Modified about-page
The about page will remain a public route but should be updated to use the auth layout (or standalone layout) for visual consistency with the new design system.

## Migration Strategy
All new migrations use strict types. The User model migration already exists — we'll add a new migration for 2FA columns rather than modifying the existing one. Sanctum's `personal_access_tokens` and `sessions` tables are published from vendor.

## Testing Approach
Tests will cover:
- Auth flows (login, register, reset, verify, 2FA) via feature tests
- Livewire component rendering and interaction
- Route middleware enforcement (auth, verified)
- Settings CRUD operations
- Rate limiting behavior
