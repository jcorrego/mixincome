<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\TransactionCategoryType;
use App\Models\TransactionCategory;
use Illuminate\Database\Seeder;

final class TransactionCategorySeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Income Categories (6)
        $this->createCategory('BUS_INCOME', 'Business Income', TransactionCategoryType::Income, 'Income from business operations');
        $this->createCategory('RENTAL_INCOME', 'Rental Income', TransactionCategoryType::Income, 'Income from rental properties');
        $this->createCategory('INTEREST_INCOME', 'Interest Income', TransactionCategoryType::Income, 'Interest earned on savings and investments');
        $this->createCategory('DIVIDEND_INCOME', 'Dividend Income', TransactionCategoryType::Income, 'Dividends from stock investments');
        $this->createCategory('CAPITAL_GAINS', 'Capital Gains', TransactionCategoryType::Income, 'Gains from sale of investments or assets');
        $this->createCategory('OTHER_INCOME', 'Other Income', TransactionCategoryType::Income, 'Other miscellaneous income');

        // Expense Categories (15)
        $this->createCategory('BUS_EXPENSE', 'Business Expense', TransactionCategoryType::Expense, 'General business expenses');
        $this->createCategory('RENTAL_EXPENSE', 'Rental Expense', TransactionCategoryType::Expense, 'Expenses related to rental properties');
        $this->createCategory('INTEREST_EXPENSE', 'Interest Expense', TransactionCategoryType::Expense, 'Interest paid on loans and credit');
        $this->createCategory('PROFESSIONAL_FEES', 'Professional Fees', TransactionCategoryType::Expense, 'Fees for professional services');
        $this->createCategory('TRAVEL_MEALS', 'Travel & Meals', TransactionCategoryType::Expense, 'Business travel and meal expenses');
        $this->createCategory('HOME_OFFICE', 'Home Office', TransactionCategoryType::Expense, 'Home office related expenses');
        $this->createCategory('INSURANCE', 'Insurance', TransactionCategoryType::Expense, 'Insurance premiums and costs');
        $this->createCategory('UTILITIES', 'Utilities', TransactionCategoryType::Expense, 'Utility bills and services');
        $this->createCategory('MAINTENANCE', 'Maintenance & Repairs', TransactionCategoryType::Expense, 'Maintenance and repair costs');
        $this->createCategory('OFFICE_SUPPLIES', 'Office Supplies', TransactionCategoryType::Expense, 'Office equipment and supplies');
        $this->createCategory('SOFTWARE_SUBS', 'Software & Subscriptions', TransactionCategoryType::Expense, 'Software licenses and subscriptions');
        $this->createCategory('MARKETING', 'Marketing & Advertising', TransactionCategoryType::Expense, 'Marketing and advertising expenses');
        $this->createCategory('LEGAL_PROF', 'Legal & Professional', TransactionCategoryType::Expense, 'Legal and professional service fees');
        $this->createCategory('TAXES_LICENSES', 'Taxes & Licenses', TransactionCategoryType::Expense, 'Business taxes and license fees');
        $this->createCategory('OTHER_EXPENSE', 'Other Expense', TransactionCategoryType::Expense, 'Other miscellaneous expenses');

        // Transfer Categories (4)
        $this->createCategory('ACCOUNT_TRANSFER', 'Account Transfer', TransactionCategoryType::Transfer, 'Transfers between accounts');
        $this->createCategory('INVESTMENT_TRANSFER', 'Investment Transfer', TransactionCategoryType::Transfer, 'Transfers to/from investment accounts');
        $this->createCategory('LOAN_PAYMENT', 'Loan Payment', TransactionCategoryType::Transfer, 'Payments towards loans');
        $this->createCategory('CC_PAYMENT', 'Credit Card Payment', TransactionCategoryType::Transfer, 'Credit card payments');

        // Tax Categories (3)
        $this->createCategory('EST_TAX_PAYMENT', 'Estimated Tax Payment', TransactionCategoryType::Tax, 'Quarterly estimated tax payments');
        $this->createCategory('WITHHOLDING_TAX', 'Withholding Tax', TransactionCategoryType::Tax, 'Tax withholdings from income');
        $this->createCategory('TAX_REFUND', 'Tax Refund', TransactionCategoryType::Tax, 'Tax refunds received');
    }

    /**
     * Create a system transaction category.
     */
    private function createCategory(
        string $code,
        string $name,
        TransactionCategoryType $type,
        string $description
    ): void {
        TransactionCategory::firstOrCreate(
            ['code' => $code],
            [
                'name' => $name,
                'category_type' => $type,
                'description' => $description,
                'is_system' => true,
            ]
        );
    }
}
