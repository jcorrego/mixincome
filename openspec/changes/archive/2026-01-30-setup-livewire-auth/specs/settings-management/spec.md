# settings-management Specification

## Purpose
User settings pages for MixIncome — profile editing, password change, appearance/dark mode toggle, account deletion, and two-factor authentication management. All settings pages use Livewire components within the authenticated app layout.

## ADDED Requirements

### Requirement: Profile Settings
The system SHALL allow authenticated users to view and update their profile information (name and email).

#### Scenario: View profile settings
- **WHEN** an authenticated user navigates to the profile settings page
- **THEN** the system SHALL display the current name and email in editable form fields

#### Scenario: Update profile name
- **WHEN** a user submits a new name on the profile settings form
- **THEN** the system SHALL update the user's name
- **AND** display a success confirmation

#### Scenario: Update profile email
- **WHEN** a user submits a new email address
- **THEN** the system SHALL update the email
- **AND** reset the email verification status (email_verified_at = null)
- **AND** send a new verification email

### Requirement: Password Settings
The system SHALL allow authenticated users to change their password.

#### Scenario: Change password successfully
- **WHEN** a user submits a valid current password and a new password with confirmation
- **THEN** the system SHALL update the password
- **AND** display a success confirmation

#### Scenario: Change password with wrong current password
- **WHEN** a user submits an incorrect current password
- **THEN** the system SHALL reject the change with a validation error

### Requirement: Appearance Settings
The system SHALL allow users to toggle between light, dark, and system appearance modes.

#### Scenario: Switch appearance mode
- **WHEN** a user selects a different appearance mode (light/dark/system)
- **THEN** the system SHALL persist the preference
- **AND** apply the theme immediately without page reload

### Requirement: Account Deletion
The system SHALL allow authenticated users to permanently delete their account.

#### Scenario: Delete account with password confirmation
- **WHEN** a user confirms account deletion by entering their current password
- **THEN** the system SHALL permanently delete the user account and all associated data
- **AND** log the user out
- **AND** redirect to the home page

#### Scenario: Delete account with wrong password
- **WHEN** a user enters an incorrect password for account deletion
- **THEN** the system SHALL reject the deletion with a validation error

### Requirement: Two-Factor Authentication Settings
The system SHALL allow users to enable, disable, and manage two-factor authentication from settings.

#### Scenario: View 2FA status
- **WHEN** a user navigates to the 2FA settings section
- **THEN** the system SHALL display whether 2FA is currently enabled or disabled

#### Scenario: Enable 2FA from settings
- **WHEN** a user enables 2FA through the settings page
- **THEN** the system SHALL generate and display a QR code for TOTP setup
- **AND** display recovery codes

#### Scenario: View recovery codes
- **WHEN** a user with 2FA enabled views their recovery codes
- **THEN** the system SHALL display the current recovery codes
- **AND** offer an option to regenerate them

#### Scenario: Regenerate recovery codes
- **WHEN** a user regenerates their recovery codes
- **THEN** the system SHALL invalidate all previous recovery codes
- **AND** display new recovery codes

#### Scenario: Disable 2FA
- **WHEN** a user disables two-factor authentication
- **THEN** the system SHALL remove the TOTP secret and recovery codes
- **AND** future logins SHALL NOT require a second factor

### Requirement: Settings Navigation
The system SHALL provide a consistent settings navigation with sections for Profile, Password, Appearance, and account management.

#### Scenario: Navigate between settings sections
- **WHEN** a user is on any settings page
- **THEN** the system SHALL display a settings sidebar/heading with links to all settings sections
- **AND** highlight the currently active section
