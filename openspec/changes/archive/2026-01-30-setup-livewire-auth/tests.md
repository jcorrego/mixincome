# Tests: setup-livewire-auth

## Test Strategy
All tests use Pest v4 with Laravel plugin. Feature tests for HTTP flows, Livewire test helpers for component interaction. Database refreshed per test via `RefreshDatabase`.

---

## 1. Authentication Tests (`tests/Feature/Auth/`)

### LoginTest.php
- [x] **test_login_page_renders** — GET `/login` returns 200 with login form
- [x] **test_users_can_login_with_valid_credentials** — POST login with valid email/password → redirect to dashboard
- [x] **test_users_cannot_login_with_invalid_credentials** — POST login with wrong password → validation error, no auth
- [x] **test_unauthenticated_root_shows_login** — GET `/` unauthenticated → renders login view
- [x] **test_authenticated_root_redirects_to_dashboard** — GET `/` authenticated → redirect to `/dashboard`

### RegistrationTest.php
- [x] **test_registration_page_renders** — GET `/register` returns 200
- [x] **test_users_can_register** — POST register with valid data → user created, authenticated, redirected
- [x] **test_registration_requires_valid_email** — POST register with invalid email → validation error
- [x] **test_registration_requires_password_confirmation** — POST register with mismatched passwords → validation error
- [x] **test_registration_rejects_duplicate_email** — POST register with existing email → validation error

### PasswordResetTest.php
- [x] **test_forgot_password_page_renders** — GET `/forgot-password` returns 200
- [x] **test_password_reset_link_can_be_requested** — POST forgot-password with valid email → notification sent
- [x] **test_password_can_be_reset_with_valid_token** — POST reset-password with valid token → password updated
- [x] **test_password_reset_with_invalid_token_fails** — POST reset-password with bad token → error

### EmailVerificationTest.php
- [x] **test_verify_email_page_renders_for_unverified_user** — GET `/email/verify` for unverified user → 200
- [x] **test_email_can_be_verified** — GET verification URL → email_verified_at set, redirect
- [x] **test_verified_user_can_access_protected_routes** — Verified user → dashboard accessible
- [x] **test_unverified_user_redirected_from_protected_routes** — Unverified user → redirected to verify page

### TwoFactorAuthenticationTest.php
- [x] **test_two_factor_challenge_page_renders** — GET `/two-factor-challenge` returns 200
- [x] **test_two_factor_can_be_enabled** — Enable 2FA → secret and recovery codes generated
- [x] **test_login_with_2fa_requires_code** — Login with 2FA enabled → redirected to challenge
- [x] **test_valid_2fa_code_completes_login** — Submit valid TOTP code → authenticated
- [x] **test_valid_recovery_code_completes_login** — Submit valid recovery code → authenticated, code invalidated

### PasswordConfirmationTest.php
- [x] **test_confirm_password_page_renders** — GET `/user/confirm-password` returns 200
- [x] **test_password_can_be_confirmed** — POST with valid password → confirmed, redirected
- [x] **test_wrong_password_not_confirmed** — POST with invalid password → validation error

### LogoutTest.php
- [x] **test_users_can_logout** — POST logout → session invalidated, redirect to login

---

## 2. Settings Tests (`tests/Feature/Settings/`)

### ProfileSettingsTest.php
- [x] **test_profile_settings_page_renders** — GET `/settings/profile` authenticated → 200
- [x] **test_profile_name_can_be_updated** — Livewire update name → saved, success feedback
- [x] **test_profile_email_can_be_updated** — Livewire update email → saved, email_verified_at reset
- [x] **test_profile_settings_requires_auth** — GET `/settings/profile` unauthenticated → redirect to login

### PasswordSettingsTest.php
- [x] **test_password_settings_page_renders** — GET `/settings/password` authenticated → 200
- [x] **test_password_can_be_changed** — Livewire submit with valid current + new password → password updated
- [x] **test_wrong_current_password_rejected** — Livewire submit with wrong current password → validation error

### AppearanceSettingsTest.php
- [x] **test_appearance_settings_page_renders** — GET `/settings/appearance` authenticated → 200

### DeleteAccountTest.php
- [x] **test_account_can_be_deleted** — Livewire confirm deletion with valid password → user deleted, logged out
- [x] **test_account_deletion_requires_correct_password** — Livewire confirm with wrong password → rejected

### TwoFactorSettingsTest.php
- [x] **test_2fa_settings_displays_status** — Component shows enabled/disabled state
- [x] **test_recovery_codes_can_be_regenerated** — Regenerate → new codes, old codes invalid

---

## 3. Layout & Navigation Tests (`tests/Feature/Layout/`)

### DashboardTest.php
- [x] **test_dashboard_renders_for_authenticated_users** — GET `/dashboard` authenticated → 200
- [x] **test_dashboard_requires_authentication** — GET `/dashboard` unauthenticated → redirect to login
- [x] **test_dashboard_requires_verified_email** — GET `/dashboard` unverified → redirect to verify

### NavigationTest.php
- [x] **test_navigation_shows_dashboard_link** — App layout contains Dashboard nav link
- [x] **test_navigation_shows_settings_link** — App layout contains Settings nav link
- [x] **test_user_menu_shows_name_and_email** — User menu displays current user info
- [x] **test_user_menu_has_logout_option** — User menu contains logout action

---

## 4. Rate Limiting Tests (`tests/Feature/Auth/`)

### RateLimitingTest.php
- [x] **test_login_rate_limited_after_5_attempts** — 6 failed logins → 429 throttled response
- [x] **test_2fa_rate_limited_after_5_attempts** — 6 failed 2FA attempts → throttled

---

## Test Count Summary
- **Auth:** 21 tests
- **Settings:** 12 tests
- **Layout:** 6 tests
- **Rate Limiting:** 2 tests
- **Total:** 41 tests
