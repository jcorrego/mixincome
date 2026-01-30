# Test Checklist: Jurisdiction Model

## 1. Feature Tests - View Jurisdictions

- [x] 1.1 Authenticated user can view jurisdictions page and see table
- [x] 1.2 Unauthenticated user is redirected to login

## 2. Feature Tests - Create Jurisdiction

- [x] 2.1 Can create jurisdiction with valid data (name, iso_code, timezone, currency)
- [x] 2.2 Cannot create jurisdiction with duplicate iso_code (unique validation)
- [x] 2.3 Cannot create jurisdiction with iso_code length != 3 (size validation)
- [x] 2.4 Cannot create jurisdiction with missing required fields (required validation)

## 3. Feature Tests - Update Jurisdiction

- [x] 3.1 Can update jurisdiction with valid data
- [x] 3.2 Cannot update jurisdiction iso_code to duplicate value
- [x] 3.3 Cannot update jurisdiction with invalid timezone

## 4. Feature Tests - Delete Jurisdiction

- [x] 4.1 Can delete jurisdiction with no dependencies
- [ ] 4.2 Cannot delete jurisdiction with dependencies (will test after related models exist)

## 5. Unit Tests - Model Behavior

- [x] 5.1 Factory creates valid jurisdiction with correct attribute types
- [x] 5.2 Fillable attributes work correctly
- [x] 5.3 Database enforces unique constraint on iso_code

## 6. Unit Tests - Seeder

- [x] 6.1 Seeder creates 3 initial jurisdictions (ES, US, CO)
- [x] 6.2 Seeder is idempotent (can run multiple times without duplicates)
- [x] 6.3 Seeder creates correct data for Spain (ES, Europe/Madrid, EUR)
- [x] 6.4 Seeder creates correct data for USA (US, America/New_York, USD)
- [x] 6.5 Seeder creates correct data for Colombia (CO, America/Bogota, COP)
