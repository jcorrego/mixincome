# Migración Velor → MixIncome

## Resumen de diferencias

### 1. Base / Starter Kit

| Aspecto | Velor | MixIncome |
|---------|-------|-----------|
| **Base** | Laravel 12 estándar | nunomaduro/laravel-starter-kit (ultra-strict, type-safe) |
| **PHP** | ^8.2 | ^8.4.0 |
| **Package manager JS** | npm | bun |
| **Strictness** | Estándar Laravel | 100% type coverage, Rector, Larastan nivel 9 |

### 2. Paquetes PHP (composer)

#### Solo en Velor (migrar)
| Paquete | Propósito | Acción |
|---------|-----------|--------|
| `laravel/fortify` v1 | Auth headless (login, register, 2FA) | ⬆️ Migrar |
| `laravel/sanctum` v4 | API tokens | ⬆️ Migrar |
| `livewire/flux` v2 | UI components (Free) | ⬆️ Migrar |
| `livewire/livewire` v4 | Reactive UI | ⬆️ Migrar |
| `livewire/volt` v1 | Single-file Livewire | ⬆️ Migrar |
| `smalot/pdfparser` | PDF text extraction | ⬆️ Migrar |
| `thiagoalessio/tesseract_ocr` | OCR para documentos | ⬆️ Migrar |
| `laravel/sail` | Docker dev | ❌ No migrar (usamos Herd) |
| `fakerphp/faker` (require) | En require, no require-dev | 🔄 Ya en MixIncome como require-dev |

#### Solo en MixIncome (mantener)
| Paquete | Propósito |
|---------|-----------|
| `nunomaduro/essentials` | Strict models, auto eager loading, immutable dates |
| `larastan/larastan` v3 | Static analysis PHP nivel 9 |
| `rector/rector` v2 | Automated refactoring |
| `driftingly/rector-laravel` | Rector rules for Laravel |
| `pestphp/pest-plugin-browser` | Browser testing (Playwright) |
| `pestphp/pest-plugin-type-coverage` | 100% type coverage enforcement |

### 3. Paquetes JS (package.json)

#### Solo en Velor
| Paquete | Propósito | Acción |
|---------|-----------|--------|
| `axios` | HTTP client | ❌ No migrar (MixIncome no lo usa) |
| `autoprefixer` | CSS postprocessing | ❌ No migrar (TW4 no lo necesita) |

#### Solo en MixIncome (mantener)
| Paquete | Propósito |
|---------|-----------|
| `prettier` | Code formatting JS/CSS |
| `prettier-plugin-organize-imports` | Import sorting |
| `prettier-plugin-tailwindcss` | TW class sorting |
| `playwright` | Browser testing |
| `npm-check-updates` | Dep update tool |

### 4. Dominio / Modelos

#### Velor tiene 22 modelos, MixIncome tiene 1 (User)

**Modelos a migrar:**
- `Account`, `Address`, `Asset`, `AssetValuation`
- `CategoryTaxMapping`, `Currency`, `DescriptionCategoryRule`
- `Document`, `DocumentTag`, `Entity`
- `Filing`, `FilingType`, `FxRate`
- `ImportBatch`, `Jurisdiction`
- `ResidencyPeriod`, `TaxYear`
- `Transaction`, `TransactionCategory`, `TransactionImport`
- `UserProfile`, `YearEndValue`

**Migraciones:** 24 en Velor → 3 en MixIncome

### 5. Arquitectura

| Aspecto | Velor | MixIncome |
|---------|-------|-----------|
| **Directorios app/** | Actions, Concerns, Console, Enums, Finance, Http, Jobs, Livewire, Models, Providers, Services | Actions, Enums, Http, Models, Providers, Services |
| **Routes** | web, finance, management, settings, console | web, console |
| **Services** | TransactionImport, Categorization, FxRate, UsTaxReporting, ColombiaTaxReporting, SpainTaxReporting | (ninguno) |
| **Auth** | Fortify + Sanctum | (ninguno aún) |
| **UI** | Livewire + Volt + Flux UI Free | Blade puro |
| **Dev environment** | Sail (Docker) + Herd | Herd |
| **DB** | MySQL 8 | (por definir) |

### 6. AI Context Files

| Archivo | Velor | MixIncome |
|---------|-------|-----------|
| `CLAUDE.md` | OpenSpec old + Laravel Boost + Architecture overview | Laravel Boost (más limpio, sin Flux/Livewire/Volt/Fortify rules) |
| `AGENTS.md` | OpenSpec old + Boost guidelines | Boost guidelines (sin OpenSpec) |
| `GEMINI.md` | ✅ | ✅ |
| `.ai/guidelines/` | git-workflow, iconography, laravel | ❌ (no existe) |
| `.junie/` | ❌ | coding-standards, guidelines |
| `.github/copilot-instructions.md` | ✅ | ✅ |
| `Plan.md` | ✅ (full product spec) | ❌ |
| `WARP.md` | ✅ | ❌ |
| `README.md` | Custom | Starter kit README |

### 7. OpenSpec Workflow

| Aspecto | Velor (viejo) | MixIncome (nuevo - OPSX) |
|---------|---------------|--------------------------|
| **Prompts** | 3: openspec-proposal, openspec-apply, openspec-archive | 10: opsx-new, opsx-continue, opsx-apply, opsx-archive, opsx-explore, opsx-ff, opsx-sync, opsx-verify, opsx-onboard, opsx-bulk-archive |
| **Prompt locations** | `.github/prompts/` + `.opencode/command/` | `.github/prompts/` + `.codex/prompts/` |
| **Config** | `openspec/AGENTS.md` + `openspec/project.md` | `openspec/config.yaml` (schema + context + rules) |
| **project.md** | Archivo separado | ❌ Reemplazado por `context:` en config.yaml |
| **AGENTS.md** | Archivo separado con instrucciones | ❌ Ya no se usa (instrucciones via prompts) |
| **Specs** | 16 capabilities | 0 (vacío) |
| **Active changes** | 6 | 0 |
| **Workflow** | 3 stages manual | Artifact-driven con schemas |

### 8. Specs existentes en Velor (a migrar)

1. `address-management`
2. `colombia-tax-reporting`
3. `currency-management`
4. `document-management`
5. `entity-management`
6. `finance-management`
7. `fx-management`
8. `import-review-management`
9. `jurisdiction-management`
10. `spain-tax-reporting`
11. `tax-form-mapping`
12. `tax-mapping-rules`
13. `tax-year-filing`
14. `ui-branding`
15. `us-tax-reporting`
16. `user-management`

### 9. Cambios activos en Velor (pendientes)

1. `add-export-packages`
2. `add-form-5472-year-end-totals`
3. `add-legal-knowledge-library`
4. `add-mercury-sync`
5. `add-multi-entity-support`
6. `add-workflow-dashboards`

---

## Plan de migración sugerido

### Fase 0: Fundamentos ✅ COMPLETADA
- [x] Configurar `openspec/config.yaml` con contexto del proyecto (migrado de Velor project.md)
- [x] Instalar paquetes base: Fortify, Sanctum, Livewire, Volt, Flux UI
- [x] Configurar auth (Fortify) — full auth flows, settings, 2FA, layouts, 60 tests passing
- [x] Actualizar CLAUDE.md con architecture overview

### Fase 0.5: UI Base ✅ COMPLETADA
- [x] Migrar logo a animado (spinning arcs)
- [x] Configura jurisdictions: migration, modelo, interfaz CRUD

### Fase 1: Schema y Modelos Core (Base Domain)
**Objetivo:** Establecer la estructura base de datos con todos los modelos core y sus relaciones. Sin esta fase, no se pueden hacer otras.

#### 1.1 User Profiles & Entities & Addresses ✅ OPENSPEC DISEÑADO
Dependencias: User (existe), Jurisdiction (✅ migrado)

**OpenSpec Change:** `phase-1-1-user-profiles-entities-addresses` (5/5 artifacts complete)
- Location: `openspec/changes/phase-1-1-user-profiles-entities-addresses/`
- Schema: tdd-driven (proposal → design → specs → tests → tasks)
- Status: Ready for implementation (`/opsx:apply`) or archive

**Modelos a crear:**

- `UserProfile` — Perfil tax para usuario + jurisdicción
  - Relaciones: belongsTo(User), belongsTo(Jurisdiction), hasMany(Entity), morphOne(Address)
  - Campos: user_id, jurisdiction_id, tax_id (SSN, RUT, NIF, etc.), status, timestamps
  - Factory + Tests
  - **Nota:** Sin metadata, sin base_currency (derivar de Jurisdiction.default_currency), sin tax_year_start

- `Entity` — Entidades legales ADICIONALES (NO Individual)
  - Types: LLC, SCorp, CCorp, Partnership, Trust, Other (sin "Individual")
  - Relaciones: belongsTo(UserProfile), morphOne(Address)
  - Campos: user_profile_id, name, entity_type (Enum), tax_id, status, timestamps
  - Factory + Tests
  - **Nota:** No se crean automáticamente; usuario las crea explícitamente

- `Address` (Polymorphic, Reutilizable, con owner)
  - Relaciones: morphTo(addressable: UserProfile, Entity, Account, Asset), belongsTo(User as owner)
  - Campos: addressable_id, addressable_type, user_id (owner), street, city, state, postal_code, country, timestamps
  - Factory + Tests
  - **Nota:** Sin tipo (no AddressType enum); una dirección por modelo; reutilizable entre modelos del mismo usuario

**Database Migrations to Create:**
```
create_user_profiles_table
create_entities_table
create_addresses_table
```

**Enums to Create:**
- `EntityType` (LLC, SCorp, CCorp, Partnership, Trust, Other) — NO Individual

#### 1.2 Tax Year Structure (FASE 1.2 - PRÓXIMA)
Dependencias: UserProfile

**Modelos a crear (cuando Fase 1.1 complete):**
- `TaxYear` — Año fiscal por UserProfile
  - Relaciones: belongsTo(UserProfile), hasMany(Filing), hasMany(Transaction) where year=TaxYear.year
  - Campos: user_profile_id, year (int), status (Enum: Draft, InProgress, Filed, Reviewed)
  - Factory + Tests

**Enums (Fase 1.2):**
- `TaxYearStatus` (Draft, InProgress, Filed, Reviewed)
- `FilingStatus` (Draft, InProgress, Submitted, Accepted, Amended, Archived)

**Nota:** ResidencyPeriod removido (complejidad diferida; implementar si necesario más adelante)

---

### Fase 2: Finance Schema (Cuentas, Transacciones, Divisas)
**Objetivo:** Sistema completo de finanzas multi-moneda con FX rates históricos.

#### 2.1 Currencies & Exchange Rates (`currencies`, `fx_rates`)
Dependencias: Ninguna

Modelos a crear:
- `Currency` — Moneda (USD, EUR, COP, etc.)
  - Relaciones: hasMany(FxRate as source), hasMany(FxRate as target)
  - Campos: code (ISO 4217), name, symbol, is_primary
  - Factory + Seeder (precargado: USD, EUR, COP, CAD, GBP)

- `FxRate` — Tasa de cambio histórica
  - Relaciones: belongsTo(Currency, 'source_currency_id'), belongsTo(Currency, 'target_currency_id')
  - Campos: source_currency_id, target_currency_id, date, rate, source (Enum: ECB, Manual, API)
  - Factory + Tests
  - Índices: (source_currency_id, target_currency_id, date) unique

**Database Migrations to Create:**
```
create_currencies_table
create_fx_rates_table
```

**Enums to Create:**
- `FxRateSource` (ECB, Manual, YNAB, Mercury, BancoSantander, Bancolombia)

---

#### 2.2 Accounts & Transactions (`accounts`, `transactions`, `transaction_categories`, `transaction_imports`)
Dependencias: Entity, TaxYear, Currency

Modelos a crear:
- `Account` — Cuenta financiera (Bank, Credit Card, Crypto wallet, etc.)
  - Relaciones: belongsTo(Entity), hasMany(Transaction), hasMany(YearEndValue)
  - Campos: entity_id, name, account_type (Enum), currency_id, account_number (encrypted), balance_opening, status
  - Factory + Tests

- `TransactionCategory` — Categoría tax-relevante (Business Income, Rental Income, Interest Expense, etc.)
  - Relaciones: hasMany(Transaction), hasMany(CategoryTaxMapping)
  - Campos: code, name, category_type (Enum: Income, Expense, Transfer, Other), description
  - Seeder (precargado con ~40 categorías estándar)

- `Transaction` — Transacción financiera
  - Relaciones: belongsTo(Account), belongsTo(TransactionCategory, nullable), belongsTo(TransactionImport), morphMany(Document)
  - Campos: account_id, category_id, import_id, date, description, amount_original, currency_original_id, amount_converted, currency_converted_id, exchange_rate, fx_rate_id (nullable), notes, metadata
  - Factory + Tests
  - Índices: (account_id, date), (category_id, date)

- `TransactionImport` — Lote de importación (CSV, QIF, API)
  - Relaciones: belongsTo(Entity), hasMany(Transaction), hasMany(Document)
  - Campos: entity_id, import_type (Enum), file_name, import_date, row_count, status (Enum: Processing, Imported, Failed, Duplicate), error_message
  - Factory + Tests

- `ImportBatch` — Batch antiguo si existe, o renombrar a TransactionImport
  - Deprecated: Mover lógica a TransactionImport

**Database Migrations to Create:**
```
create_accounts_table
create_transaction_categories_table
create_transactions_table
create_transaction_imports_table
```

**Enums to Create:**
- `AccountType` (Checking, Savings, CreditCard, Investment, Crypto, Cash, Loan, LineOfCredit)
- `TransactionCategoryType` (Income, Expense, Transfer, Tax, Other)
- `ImportType` (CSV, QIF, PDF, YNABSync, MercuryAPI, SantanderCSV, BancolombiaSFTP)
- `ImportStatus` (Processing, Imported, Failed, Duplicate, Review)

---

#### 2.3 Assets & Valuations (`assets`, `asset_valuations`, `year_end_values`)
Dependencias: Entity, TaxYear, Currency

Modelos a crear:
- `Asset` — Activo (Real Estate, Investments, Vehicles, etc.)
  - Relaciones: belongsTo(Entity), hasMany(AssetValuation), hasMany(YearEndValue)
  - Campos: entity_id, name, asset_type (Enum), acquisition_date, acquisition_cost, currency_id, location, description, status
  - Factory + Tests

- `AssetValuation` — Valuación de activo en punto en tiempo
  - Relaciones: belongsTo(Asset), belongsTo(Currency), hasMany(Document)
  - Campos: asset_id, valuation_date, value, currency_id, valuation_method (Enum: Appraisal, MarketValue, CostBasis, Other), notes
  - Factory + Tests

- `YearEndValue` — Valor resumido de activo/cuenta al final de año fiscal
  - Relaciones: belongsTo(TaxYear), morphTo(valueable) [Account o Asset]
  - Campos: tax_year_id, valueable_id, valueable_type, value, currency_id
  - Factory + Tests
  - Índice: (tax_year_id, valueable_id, valueable_type) unique

**Database Migrations to Create:**
```
create_assets_table
create_asset_valuations_table
create_year_end_values_table
```

**Enums to Create:**
- `AssetType` (RealEstate, Stock, Bond, CryptoCurrency, Vehicle, Artwork, Other)
- `ValuationMethod` (Appraisal, MarketValue, CostBasis, FairMarketValue, Other)

---

### Fase 3: Tax Reporting Schema
**Objetivo:** Mapeos tax, filings, reglas de categorización.

#### 3.1 Tax Mapping (`category_tax_mappings`, `description_category_rules`)
Dependencias: TransactionCategory, Jurisdiction, TaxYear

Modelos a crear:
- `CategoryTaxMapping` — Mapeo: TransactionCategory → Form Code → Line Item
  - Relaciones: belongsTo(TransactionCategory), belongsTo(Jurisdiction), belongsTo(TaxYear, nullable)
  - Campos: category_id, jurisdiction_id, tax_year_id, form_code (Enum: F5472, F1120, ScheduleE, IRPF, Modelo720, ColombiaDeclExt), line_item (string), description
  - Factory + Tests
  - Índice: (jurisdiction_id, category_id) unique

- `DescriptionCategoryRule` — Regla: patrones de descripción → TransactionCategory
  - Relaciones: belongsTo(TransactionCategory), belongsTo(Entity)
  - Campos: entity_id, category_id, pattern (regex), rule_type (Enum: Regex, Contains, Exact), is_active, priority
  - Factory + Tests
  - Índice: (entity_id, rule_type)

**Database Migrations to Create:**
```
create_category_tax_mappings_table
create_description_category_rules_table
```

**Enums to Create:**
- `TaxFormCode` (F5472, F1120, ScheduleE, F1040NR, F1042, IRPF, Modelo720, DeclaracionRentaColombiana, Other)
- `CategoryRuleType` (Regex, Contains, Exact, StartsWith, EndsWith)

#### 3.2 Filings (`filings`, `filing_types`)
Dependencias: TaxYear, Entity, Jurisdiction

Modelos a crear:
- `FilingType` — Tipo de declaración (Income Tax, Entity Tax, Quarterly Estimated, Amendment)
  - Campos: code, name, jurisdiction_id, form_code, required_fields
  - Seeder (precargado por jurisdicción)

- `Filing` — Declaración de impuestos completada
  - Relaciones: belongsTo(TaxYear), belongsTo(Entity), belongsTo(FilingType), hasMany(Document)
  - Campos: tax_year_id, entity_id, filing_type_id, status (Enum: Draft, InProgress, Submitted, Accepted, Amended, Archived), submission_date, filing_reference, notes, metadata
  - Factory + Tests

**Database Migrations to Create:**
```
create_filing_types_table
create_filings_table
```

---

### Fase 4: Documents & Supporting Artifacts
**Objetivo:** Sistema de documentos polimórficos (receipts, invoices, appraisals, etc.)

#### 4.1 Documents (`documents`, `document_tags`)
Dependencias: Polymorphic (Entity, Transaction, AssetValuation, Filing)

Modelos a crear:
- `Document` (Polymorphic) — Archivo (Recibo, Factura, Valuación, etc.)
  - Relaciones: morphTo(documentable), hasMany(DocumentTag), belongsTo(DocumentType)
  - Campos: documentable_id, documentable_type, document_type_id, file_path, original_filename, mime_type, file_size, uploaded_date, extracted_text (para OCR)
  - Factory + Tests

- `DocumentTag` — Etiqueta de documento (Invoice, Receipt, AppraisalReport, TaxReturn, BankStatement)
  - Relaciones: hasMany(Document)
  - Campos: name, description

**Database Migrations to Create:**
```
create_documents_table
create_document_tags_table
```

---

### Resumen de Orden de Migración Recomendado

```
1. Jurisdictions        ✅ HECHO
2. UserProfiles + Entities + Addresses    ✅ DISEÑADO (OpenSpec: phase-1-1-user-profiles-entities-addresses)
3. TaxYears (Fase 1.2)                    ← PRÓXIMO DESPUÉS DE 1.1
4. Currencies + FxRates (Fase 2.1)        ← Paralelo con 3
5. Accounts + TransactionCategories       ← Después de 3 + 4 (Fase 2.2)
6. Transactions + TransactionImports      ← Después de 5 (Fase 2.2)
7. Assets + AssetValuations + YearEndValues ← Después de 3 + 4 (Fase 2.3)
8. CategoryTaxMappings + DescriptionRules ← Después de 5 + 6 (Fase 3.1)
9. FilingTypes + Filings                  ← Después de 3 + 8 (Fase 3.2)
10. Documents + DocumentTags              ← Último (polimórfico, Fase 4)
```

---

### Fase 2: Services Layer (Después de modelos)
- [ ] FxRateService (cálculo de conversión, sincronización ECB)
- [ ] TransactionImportService (parseo CSV/PDF/QIF, detección duplicados)
- [ ] TransactionCategorizationService (rules engine, manual override)
- [ ] Migrar parsers (CSV/PDF)

### Fase 3: Tax Reporting Services (Después de tax schema)
- [ ] UsTaxReportingService (Form 5472, pro-forma 1120, Schedule E, 1040-NR)
- [ ] SpainTaxReportingService (IRPF summaries, Modelo 720)
- [ ] ColombiaTaxReportingService (Rental income summaries)

### Fase 4: UI & Controllers
- [ ] Migrar Livewire components (Dashboard, Finance, Tax modules)
- [ ] Migrar Volt pages (Settings, Filings, Reporting)
- [ ] Adaptar layouts y rutas
- [ ] Dashboard principal

### Fase 5: OpenSpec & Specs
- [ ] Migrar specs relevantes al nuevo formato OPSX
- [ ] Revisar cambios pendientes en Velor y decidir cuáles migrar
- [ ] Configurar schemas personalizados si es necesario

---

## Estado Actual (31 Enero 2026)

### ✅ Completado

| Componente | Status | Detalles |
|-----------|--------|----------|
| **Fase 0: Fundamentos** | ✅ | Auth (Fortify), Sanctum, Livewire, Volt, Flux UI, Tailwind |
| **Fase 0.5: UI Base** | ✅ | Logo animado, Jurisdictions CRUD |
| **Fase 1.1: OpenSpec Design** | ✅ | `phase-1-1-user-profiles-entities-addresses` (5/5 artifacts) |
| **Fase 1.1: Implementation** | ✅ | UserProfile, Entity, Address models + migrations + factories + 151 tests passing |

### 📋 Próximo Paso

**Fase 1.2 TaxYear Structure** (Después de 1.1)

Esto creará:
- 1 migration (tax_years)
- 1 enum (TaxYearStatus)
- 1 model (TaxYear) con relaciones a UserProfile
- ~20-30 tests

---

## Decisiones Finales Fase 1.1

### UserProfile
- ✅ user_id, jurisdiction_id, tax_id, status
- ❌ Sin metadata
- ❌ Sin base_currency (usar Jurisdiction.default_currency)
- ❌ Sin tax_year_start (diferir a Fase 1.2)

### Entity
- ✅ Solo para entidades legales (LLC, S-Corp, etc.)
- ❌ NO "Individual" (eso es UserProfile)
- ✅ No creadas automáticamente
- ✅ Pueden tener múltiples por UserProfile

### Address
- ✅ Polimorfa (UserProfile, Entity, Account, Asset)
- ✅ Reutilizable entre modelos del mismo usuario
- ✅ Owner es User (user_id para autorización)
- ❌ Sin tipo/enum (una dirección por modelo = dirección "oficial")

### Omitido
- ❌ ResidencyPeriod (diferir a más adelante)
- ❌ TaxYear (Fase 1.2)
