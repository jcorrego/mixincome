# app-layout Specification

## Purpose
Application shell and layout system for MixIncome — authenticated layout with sidebar navigation and user menu, guest/auth layout for login/register pages, and Flux UI + Livewire integration as the base UI framework.

## ADDED Requirements

### Requirement: Authenticated App Layout
The system SHALL provide an authenticated layout (`layouts/app.blade.php`) that wraps all protected pages.

#### Scenario: Render authenticated layout
- **WHEN** an authenticated user visits any protected page
- **THEN** the system SHALL render the page within the app layout
- **AND** the layout SHALL include a sidebar navigation with links to main sections
- **AND** the layout SHALL include a user dropdown menu with profile and logout options
- **AND** the layout SHALL include the page title in the header

#### Scenario: Responsive navigation
- **WHEN** the app layout renders on a mobile viewport
- **THEN** the sidebar SHALL collapse into a hamburger menu
- **AND** the navigation SHALL be accessible via the menu toggle

### Requirement: Auth Layout
The system SHALL provide a guest layout (`layouts/auth.blade.php`) for authentication pages (login, register, forgot-password, etc.).

#### Scenario: Render auth layout
- **WHEN** an unauthenticated user visits an auth page
- **THEN** the system SHALL render the page within the auth layout
- **AND** the layout SHALL center the auth form
- **AND** display the MixIncome logo

### Requirement: Flux UI Integration
The system SHALL use Flux UI Free v2 as the primary component library for consistent, accessible UI elements.

#### Scenario: Flux components available
- **WHEN** a Blade view uses Flux UI components (e.g., `<flux:button>`, `<flux:input>`, `<flux:modal>`)
- **THEN** the components SHALL render correctly with Tailwind CSS v4 styling

#### Scenario: Dark mode support
- **WHEN** the user's appearance preference is set to dark mode
- **THEN** all Flux UI components SHALL render in their dark variant

### Requirement: Livewire and Volt Integration
The system SHALL configure Livewire v4 and Volt v1 for server-driven reactive UI components.

#### Scenario: Livewire component rendering
- **WHEN** a page includes a Livewire component
- **THEN** the component SHALL render server-side and support reactive updates via websocket/polling

#### Scenario: Volt single-file components
- **WHEN** a Volt single-file component exists in the configured directory
- **THEN** it SHALL be auto-discovered and routable

### Requirement: Dashboard Page
The system SHALL provide a dashboard as the authenticated landing page.

#### Scenario: View dashboard
- **WHEN** an authenticated user navigates to `/dashboard`
- **THEN** the system SHALL render the dashboard page within the app layout
- **AND** display a welcome message or summary content

#### Scenario: Dashboard requires authentication
- **WHEN** an unauthenticated user visits `/dashboard`
- **THEN** the system SHALL redirect to the login page

### Requirement: Navigation Structure
The system SHALL provide a consistent navigation sidebar with the main application sections.

#### Scenario: Navigation links
- **WHEN** the sidebar navigation renders
- **THEN** it SHALL include at minimum: Dashboard and Settings links
- **AND** highlight the currently active section

#### Scenario: User menu
- **WHEN** the user dropdown menu is opened
- **THEN** it SHALL display the user's name and email
- **AND** include links to Settings and Logout
