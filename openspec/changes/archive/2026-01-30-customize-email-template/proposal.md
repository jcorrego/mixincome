## Why

All transactional emails (verification, password reset, etc.) currently use Laravel's default mail template with the generic Laravel logo and branding. MixIncome needs branded emails that reinforce the product identity — logo, colors, and footer — so users recognize the sender and the experience feels professional from day one.

## What Changes

- Customize the published mail HTML header (`vendor/mail/html/header.blade.php`) to display the MixIncome logo (SVG inline for email compatibility)
- Customize the mail HTML footer with MixIncome branding and copyright
- Adjust the default CSS theme (`vendor/mail/html/themes/default.css`) to use MixIncome brand colors (slate/amber accents matching the auth layout)
- Ensure the logo works in both light backgrounds (email clients) and dark mode where supported

## Capabilities

### New Capabilities
- `email-branding`: Branded transactional email template — MixIncome logo in header, brand colors, custom footer with copyright

### Modified Capabilities
_(none — this only touches the published vendor mail views, not application specs)_

## Impact

- **Views**: 3 files in `resources/views/vendor/mail/html/` (header, footer, theme CSS)
- **No PHP changes** — purely template/CSS customization
- **All notifications** (verify email, password reset, etc.) automatically pick up the new template
