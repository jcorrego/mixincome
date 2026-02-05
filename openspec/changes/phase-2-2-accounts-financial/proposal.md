# Proposal: Accounts & Financial Structure (Phase 2.2)

## Summary

Implement the core financial tracking system: accounts, transaction categories, transactions, and import batches. This builds on Phase 2.1 (currencies + FX rates) to enable multi-currency transaction tracking with tax-relevant categorization.

## Why

With currencies and FX rates in place, we need:

1. **Financial Accounts** - Users need to track multiple accounts (bank accounts, credit cards, investment accounts) across entities
2. **Transaction Categories** - Map transactions to tax-relevant categories for reporting (Business Income, Rental Income, Interest Expense, etc.)
3. **Multi-Currency Transactions** - Record transactions with original currency and lazy-convert to other currencies as needed for reports
4. **Import Batches** - Group imported transactions for better data management and error handling

Without this foundation, users cannot track their financial data or generate tax reports.

## What

Create 4 core models with full CRUD, relationships, and services:

### Models to Create

1. **Account** - Financial accounts (checking, savings, credit cards, investments, crypto)
2. **TransactionCategory** - Tax-relevant categories (pre-seeded with ~40 standard categories)  
3. **Transaction** - Individual financial transactions with multi-currency support
4. **TransactionImport** - Batch imports from CSV/QIF/API with status tracking

### Key Design Decisions

**Multi-Currency Strategy:** Use 3 hard-coded columns (`amount_usd`, `amount_eur`, `amount_cop`) instead of normalization. Lazy-fill conversions when reports need them.

**Transaction Architecture:**
- `original_currency` field tracks source currency
- Manual override allowed (user can edit converted amounts)
- Use existing FxRateService for conversions

**Categories:** Pre-seed with standard tax categories but allow custom ones.

### Affected Areas

- Database: 4 new migrations
- Models: 4 new models with relationships
- Enums: AccountType, TransactionCategoryType, ImportType, ImportStatus
- Services: CurrencyConversionService (uses existing FxRateService)
- Testing: Comprehensive factory + feature + unit tests

## Dependencies

- ✅ Phase 2.1 (Currency, FxRate models and FxRateService)
- ✅ Phase 1.1-1.2 (Entity model for account ownership)

## Risks

- **Multi-currency complexity** - 3 hard-coded columns may need schema changes for new currencies
- **Transaction volume** - Need efficient indexing for large transaction sets
- **Import reliability** - Duplicate detection and error handling critical

## Success Criteria

- [ ] All 4 models created with proper relationships
- [ ] Multi-currency transactions working with lazy conversion
- [ ] Pre-seeded transaction categories
- [ ] 100% test coverage maintained
- [ ] CurrencyConversionService integrated with existing FxRateService