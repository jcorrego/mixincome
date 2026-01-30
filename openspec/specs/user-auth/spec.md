# user-auth Specification

## Purpose
TBD - created by archiving change setup-livewire-auth. Update Purpose after archive.
## Requirements
### Requirement: User Login
The system SHALL authenticate users via email and password, with rate limiting to prevent brute force attacks.

#### Scenario: Successful login
- **WHEN** a user submits valid email and password on the login form
- **THEN** the system SHALL authenticate the user and redirect to the dashboard
- **AND** create an authenticated session

#### Scenario: Failed login with invalid credentials
- **WHEN** a user submits incorrect email or password
- **THEN** the system SHALL display an error message indicating invalid credentials
- **AND** NOT reveal whether the email exists in the system

#### Scenario: Rate-limited login attempts
- **WHEN** a user attempts login more than 5 times within 1 minute
- **THEN** the system SHALL block further attempts with a throttle error
- **AND** the throttle key SHALL combine the transliterated lowercase email and IP address

#### Scenario: Unauthenticated user visits root URL
- **WHEN** an unauthenticated user visits `/`
- **THEN** the system SHALL render the login page

#### Scenario: Authenticated user visits root URL
- **WHEN** an authenticated user visits `/`
- **THEN** the system SHALL redirect to the dashboard

### Requirement: User Registration
The system SHALL allow new users to register with name, email, and password.

#### Scenario: Successful registration
- **WHEN** a user submits a valid name, email, password, and password confirmation
- **THEN** the system SHALL create a new user account
- **AND** hash the password using Laravel's default hashing
- **AND** send an email verification notification
- **AND** redirect to the dashboard

#### Scenario: Registration with duplicate email
- **WHEN** a user submits a registration form with an email that already exists
- **THEN** the system SHALL reject the registration with a validation error

#### Scenario: Registration with weak password
- **WHEN** a user submits a password that does not meet the minimum requirements
- **THEN** the system SHALL reject the registration with a password validation error

### Requirement: Password Reset
The system SHALL allow users to reset their password via email link.

#### Scenario: Request password reset
- **WHEN** a user submits their email on the forgot-password form
- **THEN** the system SHALL send a password reset link to the email if it exists
- **AND** display a confirmation message regardless of whether the email exists

#### Scenario: Reset password with valid token
- **WHEN** a user clicks a valid password reset link and submits a new password
- **THEN** the system SHALL update the password
- **AND** redirect to the login page with a success message

#### Scenario: Reset password with expired token
- **WHEN** a user clicks an expired password reset link
- **THEN** the system SHALL display an error indicating the link has expired

### Requirement: Email Verification
The system SHALL require email verification before granting full access.

#### Scenario: Unverified user access
- **WHEN** a user with unverified email attempts to access a protected route
- **THEN** the system SHALL redirect to the email verification notice page

#### Scenario: Verify email via link
- **WHEN** a user clicks the verification link in their email
- **THEN** the system SHALL mark the email as verified
- **AND** redirect to the dashboard

#### Scenario: Resend verification email
- **WHEN** a user requests a new verification email
- **THEN** the system SHALL send a fresh verification link

### Requirement: Two-Factor Authentication
The system SHALL support TOTP-based two-factor authentication via Fortify.

#### Scenario: Enable 2FA
- **WHEN** a user enables two-factor authentication in settings
- **THEN** the system SHALL generate a TOTP secret and QR code
- **AND** display recovery codes for backup access

#### Scenario: Login with 2FA enabled
- **WHEN** a user with 2FA enabled submits valid email and password
- **THEN** the system SHALL redirect to the two-factor challenge page
- **AND** require a valid TOTP code or recovery code to complete login

#### Scenario: Rate-limited 2FA attempts
- **WHEN** a user attempts 2FA verification more than 5 times within 1 minute
- **THEN** the system SHALL block further attempts with a throttle error

#### Scenario: Login with recovery code
- **WHEN** a user submits a valid recovery code on the two-factor challenge page
- **THEN** the system SHALL authenticate the user
- **AND** invalidate the used recovery code

### Requirement: Password Confirmation
The system SHALL require password re-confirmation for sensitive operations.

#### Scenario: Access password-confirmed route
- **WHEN** a user accesses a route requiring password confirmation
- **AND** the password was NOT confirmed within the configured timeout
- **THEN** the system SHALL redirect to the confirm-password page

#### Scenario: Confirm password successfully
- **WHEN** a user submits their current valid password on the confirmation page
- **THEN** the system SHALL mark the password as confirmed
- **AND** redirect to the originally requested route

### Requirement: Logout
The system SHALL allow authenticated users to log out.

#### Scenario: User logs out
- **WHEN** an authenticated user triggers logout
- **THEN** the system SHALL invalidate the session
- **AND** redirect to the login page

### Requirement: API Token Authentication
The system SHALL support API token authentication via Laravel Sanctum for future API access.

#### Scenario: Sanctum middleware available
- **WHEN** the application boots
- **THEN** Sanctum's authentication guard SHALL be available for API routes
- **AND** the `personal_access_tokens` migration SHALL exist

