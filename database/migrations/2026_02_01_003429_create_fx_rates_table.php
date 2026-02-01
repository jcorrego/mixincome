<?php

declare(strict_types=1);

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
        Schema::create('fx_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_currency_id')->constrained('currencies')->restrictOnDelete();
            $table->foreignId('to_currency_id')->constrained('currencies')->restrictOnDelete();
            $table->date('date');
            $table->decimal('rate', 12, 8);
            $table->string('source', 50)->default('ecb');
            $table->boolean('is_replicated')->default(false);
            $table->date('replicated_from_date')->nullable();
            $table->timestamps();

            $table->unique(['from_currency_id', 'to_currency_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fx_rates');
    }
};
