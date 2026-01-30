# email-branding Specification

## Purpose
Branded transactional email template for MixIncome — custom logo, brand colors, and footer across all notification emails.

## ADDED Requirements

### Requirement: Email Header with Logo
The email header SHALL display the MixIncome logo (inline SVG) linked to the application URL.

#### Scenario: Email header renders logo
- **WHEN** any transactional email is sent (verification, password reset, etc.)
- **THEN** the email header SHALL display the MixIncome "M" logo as an inline SVG
- **AND** the logo SHALL link to the application's base URL
- **AND** the application name "MixIncome" SHALL appear next to or below the logo

#### Scenario: Logo fallback for non-SVG clients
- **WHEN** the email client does not support inline SVG
- **THEN** the header SHALL fall back to displaying the text "MixIncome"

### Requirement: Email Footer with Branding
The email footer SHALL display MixIncome branding with copyright notice.

#### Scenario: Footer content
- **WHEN** any transactional email is rendered
- **THEN** the footer SHALL include "© {year} MixIncome. All rights reserved."
- **AND** the year SHALL be dynamically generated

### Requirement: Brand Color Theme
The email CSS theme SHALL use MixIncome brand colors consistent with the application design.

#### Scenario: Primary button styling
- **WHEN** the email contains an action button (e.g., "Verify Email Address")
- **THEN** the button SHALL use the MixIncome brand primary color (slate-800 / #1e293b)
- **AND** the button text SHALL be white

#### Scenario: Header background
- **WHEN** the email header is rendered
- **THEN** it SHALL use a clean, professional style consistent with the MixIncome dark slate branding
