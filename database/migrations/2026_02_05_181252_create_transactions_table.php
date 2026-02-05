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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')
                ->constrained('accounts')
                ->onDelete('cascade');
            $table->foreignId('category_id')
                ->nullable()
                ->constrained('transaction_categories')
                ->onDelete('set null');
            $table->foreignId('import_id')
                ->nullable()
                ->constrained('transaction_imports')
                ->onDelete('set null');
            $table->date('date');
            $table->text('description');

            // Multi-currency columns (lazy-filled, nullable)
            $table->decimal('amount_usd', 15, 2)->nullable();
            $table->decimal('amount_eur', 15, 2)->nullable();
            $table->decimal('amount_cop', 15, 0)->nullable(); // COP without decimals

            $table->string('original_currency', 3); // 'USD', 'EUR', 'COP'
            
            $table->text('notes')->nullable();
            $table->timestamps();

            // Performance indexes
            $table->index(['account_id', 'date']);
            $table->index(['date', 'category_id']);
            $table->index('original_currency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
