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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('accommodation_id')
            ->constrained('accommodations')
            ->cascadeOnDelete()
            ->cascadeOnUpdate();
            $table->float('price');
            $table->float('offer_price')->default(0.00);
            $table->float('area');
            $table->integer('people_count');
            $table->longText('description');
            $table->integer('room_number');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
