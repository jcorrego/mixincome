# Tests: Phase 1.1 — User Profiles, Entities & Addresses

TDD Red phase: tests defining required behavior. All must FAIL until implementation complete.

---

## 1. User Profile Creation

- [ ] 1.1 [Unit] Can create UserProfile with user_id, jurisdiction_id, and tax_id
- [ ] 1.2 [Unit] UserProfile status defaults to "Active"
- [ ] 1.3 [Unit] Timestamps (created_at, updated_at) are auto-generated
- [ ] 1.4 [Unit] Unique constraint (user_id, jurisdiction_id) prevents duplicates
- [ ] 1.5 [Feature] POST /profiles creates new UserProfile with valid data
- [ ] 1.6 [Feature] Duplicate (user_id, jurisdiction_id) pair returns validation error

---

## 2. User Profile Retrieval

- [ ] 2.1 [Unit] Can retrieve UserProfile by ID
- [ ] 2.2 [Unit] UserProfile has eager-loaded relationship to Jurisdiction
- [ ] 2.3 [Feature] GET /profiles/:id returns UserProfile with jurisdiction details
- [ ] 2.4 [Feature] GET /users/:id/profiles lists all profiles for a user
- [ ] 2.5 [Unit] No N+1 queries when loading profiles with jurisdiction

---

## 3. User Profile Updates

- [ ] 3.1 [Unit] Can update UserProfile.tax_id without creating duplicate profile
- [ ] 3.2 [Unit] Can update UserProfile.status (Active → Inactive)
- [ ] 3.3 [Feature] PUT /profiles/:id updates tax_id successfully
- [ ] 3.4 [Feature] Updated_at timestamp changes on update

---

## 4. User Profile Deletion

- [ ] 4.1 [Unit] Deleting UserProfile cascades to related Entities
- [ ] 4.2 [Unit] Deleting UserProfile without entities works cleanly
- [ ] 4.3 [Feature] DELETE /profiles/:id removes profile and related entities

---

## 5. User Profile-Jurisdiction Relationship

- [ ] 5.1 [Unit] UserProfile.jurisdiction returns correct Jurisdiction model
- [ ] 5.2 [Unit] Jurisdiction.default_currency is accessible via profile
- [ ] 5.3 [Unit] Changing jurisdiction_id updates profile's jurisdiction

---

## 6. Entity Creation

- [ ] 6.1 [Unit] Can create Entity with user_profile_id, name, entity_type, tax_id
- [ ] 6.2 [Unit] Entity.entity_type is cast to EntityType enum
- [ ] 6.3 [Unit] Entity status defaults to "Active"
- [ ] 6.4 [Unit] Cannot create Entity with entity_type="Individual" (enum rejects it)
- [ ] 6.5 [Unit] Multiple entities can exist under same UserProfile
- [ ] 6.6 [Feature] POST /profiles/:id/entities creates entity under profile
- [ ] 6.7 [Feature] Creating UserProfile does NOT auto-create entities

---

## 7. Entity Retrieval

- [ ] 7.1 [Unit] Can retrieve Entity by ID
- [ ] 7.2 [Unit] Entity has eager-loaded relationship to UserProfile
- [ ] 7.3 [Unit] UserProfile.entities returns all entities under profile
- [ ] 7.4 [Feature] GET /entities/:id returns Entity with profile context
- [ ] 7.5 [Feature] GET /profiles/:id/entities lists entities for profile
- [ ] 7.6 [Unit] No N+1 queries when loading entities with profile

---

## 8. Entity Updates

- [ ] 8.1 [Unit] Can update Entity.name
- [ ] 8.2 [Unit] Can update Entity.status (Active → Inactive)
- [ ] 8.3 [Unit] Can update Entity.tax_id
- [ ] 8.4 [Feature] PUT /entities/:id updates entity fields
- [ ] 8.5 [Unit] Updated_at timestamp changes on update

---

## 9. Entity Deletion

- [ ] 9.1 [Unit] Deleting Entity cascades to related Accounts (Fase 2)
- [ ] 9.2 [Unit] Deleting Entity cascades to related Address if exists
- [ ] 9.3 [Feature] DELETE /entities/:id removes entity
- [ ] 9.4 [Unit] Deleting Entity does NOT cascade to UserProfile (parent not affected)

---

## 10. Entity-UserProfile Relationship

- [ ] 10.1 [Unit] Entity.userProfile returns correct UserProfile
- [ ] 10.2 [Unit] Entity inherits jurisdiction via Entity.userProfile.jurisdiction
- [ ] 10.3 [Unit] Cannot change entity's user_profile_id after creation (immutable parent)

---

## 11. Entity Types (Enum)

- [ ] 11.1 [Unit] EntityType enum contains: LLC, SCorp, CCorp, Partnership, Trust, Other
- [ ] 11.2 [Unit] EntityType enum does NOT contain Individual
- [ ] 11.3 [Unit] Entity.entity_type is type-safe (enum, not string)
- [ ] 11.4 [Unit] Creating Entity with invalid type rejects request

---

## 12. Entity Tax ID Requirement

- [ ] 12.1 [Unit] tax_id is required when creating Entity
- [ ] 12.2 [Feature] POST /entities without tax_id returns validation error

---

## 13. Address Creation

- [ ] 13.1 [Unit] Can create Address with street, city, state, postal_code, country
- [ ] 13.2 [Unit] Address is polymorphic (addressable_type, addressable_id)
- [ ] 13.3 [Unit] Address has user_id (owner) for authorization
- [ ] 13.4 [Unit] Address country is required (validation fails without it)
- [ ] 13.5 [Unit] Can associate Address with UserProfile (polymorphic)
- [ ] 13.6 [Unit] Can associate Address with Entity (polymorphic)
- [ ] 13.7 [Feature] POST /addresses creates address and associates to addressable
- [ ] 13.8 [Feature] Validation requires street, city, state, postal_code, country

---

## 14. Address Reuse

- [ ] 14.1 [Unit] Same Address row can be referenced by UserProfile and Entity
- [ ] 14.2 [Unit] User.addresses returns all addresses owned by user (inverse polymorphic)
- [ ] 14.3 [Feature] GET /users/:id/addresses lists all addresses for user
- [ ] 14.4 [Unit] Reusing address avoids data duplication

---

## 15. Address Retrieval

- [ ] 15.1 [Unit] Can retrieve Address by ID
- [ ] 15.2 [Unit] UserProfile.address returns associated Address (morphOne)
- [ ] 15.3 [Unit] Entity.address returns associated Address (morphOne)
- [ ] 15.4 [Unit] Address.addressable returns correct polymorphic model
- [ ] 15.5 [Feature] GET /addresses/:id returns Address with addressable context
- [ ] 15.6 [Unit] No N+1 queries when loading addresses with relationships

---

## 16. Address Updates

- [ ] 16.1 [Unit] Can update Address.street, city, state, postal_code, country
- [ ] 16.2 [Feature] PUT /addresses/:id updates address fields
- [ ] 16.3 [Unit] Updated_at timestamp changes on update

---

## 17. Address Deletion

- [ ] 17.1 [Unit] Can delete Address
- [ ] 17.2 [Feature] DELETE /addresses/:id removes address
- [ ] 17.3 [Unit] Deleting Entity with address deletes the address (cascade)

---

## 18. Address Polymorphism

- [ ] 18.1 [Unit] Address.addressable_type can be 'App\Models\UserProfile'
- [ ] 18.2 [Unit] Address.addressable_type can be 'App\Models\Entity'
- [ ] 18.3 [Unit] Address.addressable_type can be 'App\Models\Account' (Fase 2+)
- [ ] 18.4 [Unit] Address.addressable_type can be 'App\Models\Asset' (Fase 2+)
- [ ] 18.5 [Unit] Address.addressable returns actual model (UserProfile, Entity, etc.)

---

## 19. Address User Ownership

- [ ] 19.1 [Unit] Address.user_id identifies owner
- [ ] 19.2 [Unit] All user addresses have same user_id
- [ ] 19.3 [Feature] GET /addresses/:id respects user ownership (auth boundary)

---

## 20. Database Constraints & Indexes

- [ ] 20.1 [Unit] Migration creates user_profiles table with correct columns
- [ ] 20.2 [Unit] Migration creates unique index on (user_id, jurisdiction_id)
- [ ] 20.3 [Unit] Migration creates entities table with correct columns
- [ ] 20.4 [Unit] Migration creates addresses table with polymorphic columns
- [ ] 20.5 [Unit] Foreign keys are correctly configured (cascade deletes)

---

## 21. Factories & Realistic Data

- [ ] 21.1 [Unit] UserProfileFactory generates realistic profile with valid jurisdiction
- [ ] 21.1 [Unit] UserProfileFactory generates jurisdiction-specific tax_id formats
- [ ] 21.3 [Unit] EntityFactory generates valid EntityType enum
- [ ] 21.4 [Unit] EntityFactory generates realistic EIN-like tax_id
- [ ] 21.5 [Unit] AddressFactory generates realistic street, city, state, country
- [ ] 21.6 [Unit] Factories can be chained (UserProfile → Entities → Addresses)

---

## 22. Type Coverage

- [ ] 22.1 [Unit] All model properties have explicit type declarations
- [ ] 22.2 [Unit] All relationships have return type hints
- [ ] 22.3 [Unit] All casts are explicitly typed
- [ ] 22.4 [Unit] 100% type coverage enforced by Pest plugin

---

## 23. Model Relationships - Complete Hierarchy

- [ ] 23.1 [Unit] User.userProfiles returns hasMany(UserProfile)
- [ ] 23.2 [Unit] UserProfile.user returns belongsTo(User)
- [ ] 23.3 [Unit] UserProfile.jurisdiction returns belongsTo(Jurisdiction)
- [ ] 23.4 [Unit] UserProfile.entities returns hasMany(Entity)
- [ ] 23.5 [Unit] UserProfile.address returns morphOne(Address)
- [ ] 23.6 [Unit] Entity.userProfile returns belongsTo(UserProfile)
- [ ] 23.7 [Unit] Entity.address returns morphOne(Address)
- [ ] 23.8 [Unit] Address.addressable returns morphTo()
- [ ] 23.9 [Unit] Address.user returns belongsTo(User)

---

## 24. Integration Tests (End-to-End)

- [ ] 24.1 [Feature] Can create User → UserProfile → Entity → Address in sequence
- [ ] 24.2 [Feature] Full data flow: User has 2 profiles (Spain, USA), Spain has 1 entity with address, USA has 2 entities
- [ ] 24.3 [Feature] Deleting profile cascades correctly to entities and addresses
- [ ] 24.4 [Feature] Querying full hierarchy with eager loading is efficient (no N+1)

---

## 25. Enum Tests

- [ ] 25.1 [Unit] EntityType enum can be instantiated with LLC
- [ ] 25.2 [Unit] EntityType enum can be instantiated with SCorp
- [ ] 25.3 [Unit] EntityType enum cannot be instantiated with Individual (throws error or validation fails)
- [ ] 25.4 [Unit] EntityType.cases() returns all valid types

---
