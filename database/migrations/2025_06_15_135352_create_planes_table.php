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
        Schema::create('planes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('airline_id')
            ->constrained('air_lines')
            ->cascadeOnDelete()
            ->cascadeOnUpdate();
            $table->foreignId('plane_type_id')
            ->constrained('plan_types')
            ->cascadeOnDelete()
            ->cascadeOnUpdate();
            $table->integer("seats_count");
            $table->string("plane_shape_diagram");
            $table->string("status");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planes');
    }
};
