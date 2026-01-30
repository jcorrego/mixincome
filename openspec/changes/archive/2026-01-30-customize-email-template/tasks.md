# Tasks: customize-email-template

## Phase 1: Template Customization

- [x] **T01** Update `resources/views/vendor/mail/html/header.blade.php` — replace Laravel logo with MixIncome inline SVG logo + "MixIncome" text, linked to app URL
- [x] **T02** Update `resources/views/vendor/mail/html/footer.blade.php` — branded footer with dynamic year copyright
- [x] **T03** Update `resources/views/vendor/mail/html/themes/default.css` — brand colors for buttons (#1e293b), links, and header styling

## Phase 2: Tests

- [x] **T04** Write `tests/Feature/Mail/EmailBrandingTest.php` — 4 tests verifying logo, footer, button color, and cross-notification branding

## Phase 3: Verify

- [x] **T05** Send test verification email and confirm rendering in Mailpit
- [x] **T06** Send test password reset email and confirm rendering in Mailpit
- [x] **T07** Run `composer test` — all tests pass
