# Proposal: Phase 1.1 — User Profiles, Entities & Addresses

## Why

Fase 0 completó la base (auth, jurisdictions, Livewire setup). Fase 1.1 establece el **core domain model** para MixIncome: la estructura que modela usuarios con múltiples jurisdicciones, entidades legales, y direcciones compartidas.

Sin este foundation, no podemos construir finanzas (Fase 2), tax reporting (Fase 3), ni UI (Fase 4). Esta es la base sobre la que descansa todo.

## What Changes

### New Models & Migrations

**UserProfile** — Perfil tax de un usuario en una jurisdicción específica
- Tablas: `user_profiles`
- Un user puede tener múltiples profiles (uno por jurisdicción, máximo)
- Contenedor central para todo lo "legal" de ese usuario en esa jurisdicción
- Campos: `user_id`, `jurisdiction_id`, `tax_id` (SSN, RUT, NIF, etc.), `status`, timestamps

**Entity** — Entidades legales adicionales (LLC, S-Corp, Corporation, Partnership, Trust)
- Tablas: `entities`
- Solo para entidades legales distintas del usuario mismo (NO "Individual")
- Pertenecen a un UserProfile
- No se crean automáticamente; el usuario las crea explícitamente
- Campos: `user_profile_id`, `name`, `entity_type` (Enum), `tax_id`, `status`, timestamps

**Address** — Direcciones reutilizables (polimorfa)
- Tablas: `addresses`
- Cada modelo (UserProfile, Entity, Account, Asset) puede tener una dirección
- Reutilizable entre modelos del mismo usuario (no duplicar direcciones)
- Owner es el usuario (`user_id`) para respetar privacidad/permisos
- Campos: `addressable_id`, `addressable_type`, `user_id`, `street`, `city`, `state`, `postal_code`, `country`, timestamps

### New Enums

- **EntityType**: LLC, SCorp, CCorp, Partnership, Trust, Other

### Factories & Tests

- Factory para cada modelo (realistic data generation)
- Tests para relaciones, polymorphism, validaciones
- Pruebas de eager loading y N+1 queries

## Capabilities

### New Capabilities

- `user-profile-management`: Crear, editar, listar perfiles tax por usuario/jurisdicción. Gestión de `tax_id` por profile.
- `entity-management`: Crear, editar, listar entidades legales (LLC, S-Corp, etc.) bajo un profile. No automático.
- `address-management`: Crear, reutilizar, editar direcciones polimórficas. Una dirección por modelo. Owner es el usuario.

### Modified Capabilities

(None — estas son nuevas capacidades, no modificaciones de existentes)

## Impact

- **DB**: 3 nuevas tablas (`user_profiles`, `entities`, `addresses`), 1 nuevo enum (`EntityType`)
- **Code**: 3 modelos, 3 factories, 1 enum, migration files
- **Relaciones**: Todas las futuras fases (Fase 2+) dependen de UserProfile + Entity como "owners" de datos
- **Tests**: ~60 nuevos tests (relationships, factory generation, validation)
- **No breaking changes**: User model existe ya; estas son purely additive

## Notes

- Address es polimorfa pero tiene un owner (`user_id`) explícito para control de acceso
- UserProfile sin `metadata` — simplificar por ahora
- UserProfile sin `base_currency` — derivar de `Jurisdiction.default_currency`
- UserProfile sin `tax_year_start` — añadir en Fase 1.2 si se necesita
- Entity only para entidades legales, no Individual (eso es el User)
