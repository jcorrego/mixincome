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
        Schema::create('transaction_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->enum('category_type', [
                'Income',
                'Expense',
                'Transfer',
                'Tax',
                'Other',
            ]);
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestamps();

            // Indexes for performance
            $table->index('category_type');
            $table->index('is_system');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_categories');
    }
};
