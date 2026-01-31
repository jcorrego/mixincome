## MODIFIED Requirements

### Requirement: Address Association for Profiles

When creating or editing a profile, the user SHALL select an address (existing or create new). Each profile can have one address. The address dropdown SHALL display each address using its `display_label` accessor (format: "street, city (country)").

#### Scenario: Create profile with existing address
- **WHEN** user creates a profile and selects an existing address from the "Address" dropdown
- **THEN** the dropdown SHALL display each address as "street, city (country)"
- **THEN** system associates the address to the profile (sets address_id), creates profile successfully

#### Scenario: Create profile without address
- **WHEN** user creates a profile and leaves the "Address" dropdown empty
- **THEN** system creates the profile with address_id = NULL (address is optional)

#### Scenario: Navigate to address management
- **WHEN** user clicks "Create new address" link in the profile form
- **THEN** system navigates to `/management/addresses` page (no profile creation happens, form is abandoned)

#### Scenario: Edit profile and change address
- **WHEN** user edits a profile and changes the address (selects a different address from dropdown)
- **THEN** the dropdown SHALL display each address as "street, city (country)"
- **THEN** system updates the profile.address_id to the new address, displays success message
