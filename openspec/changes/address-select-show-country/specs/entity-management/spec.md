## MODIFIED Requirements

### Requirement: Address Association for Entities

When creating or editing an entity, the user SHALL select an address (existing or create new). Each entity can have one address. The address dropdown SHALL display each address using its `display_label` accessor (format: "street, city (country)").

#### Scenario: Create entity with existing address
- **WHEN** user creates an entity and selects an existing address from the "Address" dropdown
- **THEN** the dropdown SHALL display each address as "street, city (country)"
- **THEN** system associates the address to the entity (sets address_id), creates entity successfully

#### Scenario: Create entity without address
- **WHEN** user creates an entity and leaves the "Address" dropdown empty
- **THEN** system creates the entity with address_id = NULL (address is optional)

#### Scenario: Navigate to address management from entity form
- **WHEN** user clicks "Create new address" link in the entity form
- **THEN** system navigates to `/management/addresses` page (no entity creation happens, form is abandoned)

#### Scenario: Edit entity and change address
- **WHEN** user edits an entity and changes the address (selects a different address from dropdown)
- **THEN** the dropdown SHALL display each address as "street, city (country)"
- **THEN** system updates the entity.address_id to the new address, displays success message
