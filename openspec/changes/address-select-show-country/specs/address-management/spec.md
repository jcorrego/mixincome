## ADDED Requirements

### Requirement: Address Display Label Accessor

The Address model SHALL provide a `display_label` attribute accessor that returns a formatted string combining street, city, and country for use in dropdowns and other display contexts.

#### Scenario: Display label format
- **WHEN** an Address has street = "123 Main St", city = "Miami", country = "US"
- **THEN** the `display_label` attribute SHALL return "123 Main St, Miami (US)"

#### Scenario: Display label used in selection dropdowns
- **WHEN** a form displays an address selection dropdown (UserProfile or Entity forms)
- **THEN** each address option SHALL display the `display_label` value

## MODIFIED Requirements

### Requirement: Address Display in Profile/Entity Forms

When users create or edit a UserProfile or Entity, they can select an existing address from a dropdown. System SHALL display available addresses in a user-friendly way, including the country.

#### Scenario: Address dropdown shows available addresses
- **WHEN** user opens the profile/entity creation form and views the "Address" dropdown
- **THEN** system displays: "(None)", then all available unassociated addresses (where all FK fields are NULL), then separators or disabled options for associated addresses (grayed out, with "used by..." tooltip)
- **THEN** each address option SHALL display in the format: "street, city (country)" using the Address model's `display_label` accessor

#### Scenario: User can leave address empty
- **WHEN** user submits a profile/entity creation form without selecting an address
- **THEN** system creates the model with address_id = NULL
