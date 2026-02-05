<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entity_id')
                ->constrained('entities')
                ->onDelete('cascade');
            $table->string('name');
            $table->enum('account_type', [
                'Checking',
                'Savings',
                'CreditCard',
                'Investment',
                'Crypto',
                'Cash',
                'Loan',
                'LineOfCredit',
            ]);
            $table->foreignId('currency_id')
                ->constrained('currencies')
                ->onDelete('restrict');
            $table->text('account_number')->nullable();
            $table->decimal('balance_opening', 15, 2)->nullable();
            $table->enum('status', ['Active', 'Inactive', 'Closed'])
                ->default('Active');
            $table->timestamps();

            // Indexes for performance
            $table->index(['entity_id', 'status']);
            $table->index('account_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
