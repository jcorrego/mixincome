## Why

MixIncome currently has no authentication, no reactive UI framework, and no API token support. It's a bare Laravel 12 install with only two static Blade views (`welcome`, `about`). To build any user-facing functionality — accounts, transactions, tax forms — we first need:

1. **Authentication** — login, register, password reset, email verification, 2FA
2. **Reactive UI** — Livewire + Volt for server-driven interactive components
3. **UI Components** — Flux UI Free for consistent, accessible design system
4. **API Tokens** — Sanctum for future API/mobile access

This is the foundation that every subsequent feature depends on. Migrating from Velor, which already has this stack working.

## What Changes

- Install and configure **Livewire v4**, **Volt v1**, and **Flux UI Free v2** packages
- Install and configure **Fortify v1** for headless authentication (login, register, password reset, email verification, 2FA)
- Install and configure **Sanctum v4** for API token authentication
- Create **FortifyServiceProvider** with view bindings, rate limiting, and action classes
- Create **Fortify action classes**: `CreateNewUser`, `ResetUserPassword` (adapted to strict types/PHP 8.4)
- Create **auth Blade views** using Livewire + Flux UI: login, register, forgot-password, reset-password, verify-email, confirm-password, two-factor-challenge
- Create **app layout** (`layouts/app.blade.php`) with navigation, user menu, and Flux UI styling
- Create **auth layout** (`layouts/auth.blade.php`) for guest pages
- Create **settings pages**: profile, password, appearance, delete account, two-factor setup
- Create **dashboard** view (authenticated landing page)
- Update **routes/web.php** with auth-guarded routes and redirect logic
- Update **bootstrap/app.php** with any required middleware
- Adapt **User model** to support Fortify's `TwoFactorAuthenticatable` trait and Sanctum's `HasApiTokens`
- Add required **database migrations** (2FA columns, personal access tokens)
- Update **Tailwind/Vite** config for Livewire compatibility

## Capabilities

### New Capabilities
- `user-auth`: User authentication flows — login, register, password reset, email verification, two-factor authentication, password confirmation
- `settings-management`: User settings pages — profile editing, password change, appearance (dark mode), account deletion, 2FA management
- `app-layout`: Application shell — authenticated layout with navigation sidebar, user dropdown menu, auth layout for guest pages, Flux UI integration

### Modified Capabilities
- `about-page`: Layout will change from standalone Blade to use the new app/auth layouts

## Impact

- **Dependencies**: +4 composer packages (fortify, sanctum, livewire, flux), +0 JS packages (Livewire is PHP-driven)
- **Database**: New migrations for `two_factor_*` columns on users, `personal_access_tokens` table, `sessions` table
- **User model**: Adds `TwoFactorAuthenticatable`, `HasApiTokens` traits; new columns
- **Routes**: Complete restructure from 2 public routes to auth-guarded route groups
- **Views**: From 2 static Blade files to full Livewire component tree (~20+ files)
- **Config**: New `fortify.php`, `sanctum.php` config files; updated `app.php` providers
