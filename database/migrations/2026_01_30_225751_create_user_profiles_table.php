<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('restrict')->onUpdate('cascade');
            $table->foreignId('jurisdiction_id')->constrained()->onDelete('restrict')->onUpdate('cascade');
            $table->string('tax_id');
            $table->string('status')->default('Active');
            $table->timestamps();

            // Unique constraint: a user can have at most one profile per jurisdiction
            $table->unique(['user_id', 'jurisdiction_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
