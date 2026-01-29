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

### Fase 0: Fundamentos
- [x] Configurar `openspec/config.yaml` con contexto del proyecto (migrado de Velor project.md)
- [ ] Instalar paquetes base: Fortify, Sanctum, Livewire, Volt, Flux UI
- [ ] Configurar auth (Fortify)
- [ ] Actualizar CLAUDE.md con architecture overview

### Fase 1: Schema y Modelos Core
- [ ] Migrar migraciones de DB (adaptar a strict types)
- [ ] Migrar modelos con factories y seeders
- [ ] Migrar Enums
- [ ] Migrar relaciones y casts

### Fase 2: Services Layer
- [ ] FxRateService
- [ ] TransactionImportService
- [ ] TransactionCategorizationService
- [ ] Migrar parsers (CSV/PDF)

### Fase 3: Tax Reporting
- [ ] UsTaxReportingService
- [ ] SpainTaxReportingService
- [ ] ColombiaTaxReportingService
- [ ] CategoryTaxMapping

### Fase 4: UI
- [ ] Migrar Livewire components
- [ ] Migrar Volt pages
- [ ] Adaptar layouts y rutas
- [ ] Dashboard

### Fase 5: OpenSpec
- [ ] Migrar specs relevantes al nuevo formato OPSX
- [ ] Revisar cambios pendientes y decidir cuáles migrar
- [ ] Configurar schemas personalizados si es necesario
