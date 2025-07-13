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
        Schema::create('accommodations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')
            ->constrained('owners')
            ->cascadeOnDelete()
            ->cascadeOnUpdate();
            $table->foreignId('accommodation_type_id')
            ->constrained('accommodation_types')
            ->cascadeOnDelete()
            ->cascadeOnUpdate();
            $table->string('accommodation_name')->nullable();
            $table->float('price')->nullable();
            $table->float('offer_price')->nullable();
            $table->float('area')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accommodations');
    }
};
