# Tasks: setup-livewire-auth

## Phase 1: Package Installation & Configuration

- [ ] **T01** Install composer packages: `composer require laravel/fortify:^1.30 laravel/sanctum:^4.2 livewire/livewire:^4.0 livewire/volt:^1.10 livewire/flux:^2.9`
- [ ] **T02** Publish Fortify config and migrations: `php artisan vendor:publish --provider="Laravel\Fortify\FortifyServiceProvider"`
- [ ] **T03** Publish Sanctum config and migrations: `php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"`
- [ ] **T04** Run Livewire config publish: `php artisan livewire:publish --config`
- [ ] **T05** Run Volt install: `php artisan volt:install`
- [ ] **T06** Run Flux install: `php artisan flux:install`
- [ ] **T07** Run migrations: `php artisan migrate`
- [ ] **T08** Verify Fortify features in `config/fortify.php`: enable registration, resetPasswords, emailVerification, twoFactorAuthentication (with confirmPassword)

## Phase 2: User Model & Auth Actions

- [ ] **T09** Update `User` model: add `TwoFactorAuthenticatable` trait (from Fortify), `HasApiTokens` trait (from Sanctum), update `$casts` and `$hidden` for 2FA columns
- [ ] **T10** Create `app/Actions/Fortify/CreateNewUser.php` — validate name, email, password; create user with hashed password. Strict types, PHP 8.4 style.
- [ ] **T11** Create `app/Actions/Fortify/ResetUserPassword.php` — validate and update password. Strict types, PHP 8.4 style.
- [ ] **T12** Create `app/Providers/FortifyServiceProvider.php` — register view bindings, actions, rate limiting (login: 5/min by email+IP, 2FA: 5/min by session). Register in `bootstrap/providers.php`.
- [ ] **T13** Create `app/Livewire/Actions/Logout.php` — invalidate session, regenerate token, redirect to `/`.

## Phase 3: Layouts & Components

- [ ] **T14** Create `resources/views/layouts/app.blade.php` — authenticated layout with Flux sidebar, header with user dropdown, main content slot. Include Livewire styles/scripts.
- [ ] **T15** Create `resources/views/layouts/auth.blade.php` — guest layout with centered card, MixIncome logo, form slot.
- [ ] **T16** Create `resources/views/partials/head.blade.php` — shared `<head>` with Vite assets, Livewire styles, Flux styles.
- [ ] **T17** Create `resources/views/components/app-logo.blade.php` and `app-logo-icon.blade.php` — MixIncome logo with dark mode support.
- [ ] **T18** Create `resources/views/components/auth-header.blade.php` — header component for auth pages (logo + title).
- [ ] **T19** Create `resources/views/components/desktop-user-menu.blade.php` — user dropdown with name, email, settings link, logout.

## Phase 4: Auth Views (Livewire)

- [ ] **T20** Create `resources/views/livewire/auth/login.blade.php` — email/password form with "Forgot password?" and "Register" links. Uses auth layout.
- [ ] **T21** Create `resources/views/livewire/auth/register.blade.php` — name/email/password/confirm form. Uses auth layout.
- [ ] **T22** Create `resources/views/livewire/auth/forgot-password.blade.php` — email input for password reset link request.
- [ ] **T23** Create `resources/views/livewire/auth/reset-password.blade.php` — new password form with token.
- [ ] **T24** Create `resources/views/livewire/auth/verify-email.blade.php` — verification notice with resend button.
- [ ] **T25** Create `resources/views/livewire/auth/confirm-password.blade.php` — password confirmation form.
- [ ] **T26** Create `resources/views/livewire/auth/two-factor-challenge.blade.php` — TOTP code or recovery code input.

## Phase 5: Settings Livewire Components & Views

- [ ] **T27** Create `app/Livewire/Settings/Profile.php` + `resources/views/livewire/settings/profile.blade.php` — name/email editing with validation and save feedback.
- [ ] **T28** Create `app/Livewire/Settings/Password.php` + `resources/views/livewire/settings/password.blade.php` — current password + new password change form.
- [ ] **T29** Create `app/Livewire/Settings/Appearance.php` + `resources/views/livewire/settings/appearance.blade.php` — light/dark/system theme toggle.
- [ ] **T30** Create `app/Livewire/Settings/DeleteUserForm.php` + `resources/views/livewire/settings/delete-user-form.blade.php` — account deletion with password confirmation modal.
- [ ] **T31** Create `app/Livewire/Settings/TwoFactor.php` + `resources/views/livewire/settings/two-factor.blade.php` — enable/disable 2FA, QR code display, recovery codes.
- [ ] **T32** Create `resources/views/partials/settings-heading.blade.php` — settings page nav tabs (Profile, Password, Appearance).
- [ ] **T33** Create settings route views: `resources/views/settings/profile.blade.php`, `password.blade.php`, `appearance.blade.php` — wrappers that use app layout and include the Livewire component.

## Phase 6: Routes & Middleware

- [ ] **T34** Update `routes/web.php` — root redirects to dashboard (auth) or login (guest), dashboard route with `['auth', 'verified']` middleware.
- [ ] **T35** Create `routes/settings.php` — settings group with `['auth', 'verified']` middleware: profile, password, appearance.
- [ ] **T36** Update `bootstrap/app.php` if needed for any middleware registration.
- [ ] **T37** Update `resources/views/about.blade.php` to use consistent styling with new design system (keep as public route).

## Phase 7: Tests (Red → Green)

- [ ] **T38** Write auth feature tests: `tests/Feature/Auth/LoginTest.php` (5 tests)
- [ ] **T39** Write auth feature tests: `tests/Feature/Auth/RegistrationTest.php` (5 tests)
- [ ] **T40** Write auth feature tests: `tests/Feature/Auth/PasswordResetTest.php` (4 tests)
- [ ] **T41** Write auth feature tests: `tests/Feature/Auth/EmailVerificationTest.php` (4 tests)
- [ ] **T42** Write auth feature tests: `tests/Feature/Auth/TwoFactorAuthenticationTest.php` (5 tests)
- [ ] **T43** Write auth feature tests: `tests/Feature/Auth/PasswordConfirmationTest.php` (3 tests)
- [ ] **T44** Write auth feature tests: `tests/Feature/Auth/LogoutTest.php` (1 test)
- [ ] **T45** Write settings tests: `tests/Feature/Settings/ProfileSettingsTest.php` (4 tests)
- [ ] **T46** Write settings tests: `tests/Feature/Settings/PasswordSettingsTest.php` (3 tests)
- [ ] **T47** Write settings tests: `tests/Feature/Settings/AppearanceSettingsTest.php` (1 test)
- [ ] **T48** Write settings tests: `tests/Feature/Settings/DeleteAccountTest.php` (2 tests)
- [ ] **T49** Write settings tests: `tests/Feature/Settings/TwoFactorSettingsTest.php` (2 tests)
- [ ] **T50** Write layout tests: `tests/Feature/Layout/DashboardTest.php` (3 tests)
- [ ] **T51** Write layout tests: `tests/Feature/Layout/NavigationTest.php` (4 tests)
- [ ] **T52** Write rate limiting tests: `tests/Feature/Auth/RateLimitingTest.php` (2 tests)

## Phase 8: Quality & Finalization

- [ ] **T53** Run `vendor/bin/pint` — fix code style
- [ ] **T54** Run `vendor/bin/phpstan analyse` — fix static analysis issues (level 9)
- [ ] **T55** Run `composer test` — full suite (type coverage + unit + lint + static)
- [ ] **T56** Update `MIGRATION.md` — mark Phase 0 as complete
