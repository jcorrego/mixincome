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
        Schema::create('transaction_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entity_id')
                ->constrained('entities')
                ->onDelete('cascade');
            $table->enum('import_type', [
                'CSV',
                'QIF',
                'PDF',
                'YNABSync',
                'MercuryAPI',
                'SantanderCSV',
                'BancolombiaSFTP',
            ]);
            $table->string('file_name')->nullable(); // NULL for API imports
            $table->timestamp('import_date')->useCurrent();
            $table->unsignedInteger('row_count')->default(0);
            $table->enum('status', [
                'Processing',
                'Imported',
                'Failed',
                'Duplicate',
                'Review',
            ])->default('Processing');
            $table->text('error_message')->nullable();
            $table->timestamps();

            // Performance indexes
            $table->index(['entity_id', 'status']);
            $table->index('import_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_imports');
    }
};
