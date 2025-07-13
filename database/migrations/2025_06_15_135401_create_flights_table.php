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
        Schema::create('flights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('air_line_id')
            ->constrained('air_lines')
            ->cascadeOnDelete()
            ->cascadeOnUpdate();
            $table->foreignId('plane_id')
            ->constrained('planes')
            ->cascadeOnDelete()
            ->cascadeOnUpdate();
            $table->float('price');
            $table->float('offer_price');
            $table->string('flight_number');
            $table->string('starting_point_location');
            $table->string('landing_point_location');
            $table->string('starting_airport');
            $table->string('landing_airport');
            $table->string('start_time');
            $table->string('land_time');
            $table->string('estimated_time');
            $table->date('date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flights');
    }
};
