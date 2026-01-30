# Tests: customize-email-template

## Test Strategy
Feature tests that trigger real notification emails and assert on rendered content. Use `Notification::fake()` where appropriate, and direct Markdown rendering for template output checks.

---

## 1. Email Branding Tests (`tests/Feature/Mail/`)

### EmailBrandingTest.php
- [x] **test_verification_email_contains_mixincome_logo** — Render the email verification notification and assert the output contains the MixIncome SVG logo or "MixIncome" text
- [x] **test_verification_email_contains_branded_footer** — Assert footer contains "MixIncome" and current year copyright
- [x] **test_verification_email_has_branded_button_color** — Assert the rendered HTML contains the slate-800 button color (#1e293b)
- [x] **test_password_reset_email_contains_mixincome_branding** — Trigger password reset and assert logo + footer present

---

## Test Count Summary
- **Email Branding:** 4 tests
- **Total:** 4 tests
