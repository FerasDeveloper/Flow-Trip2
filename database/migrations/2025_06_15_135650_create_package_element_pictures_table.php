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
        Schema::create('package_element_pictures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_element_id')
            ->constrained('package_elements')
            ->cascadeOnDelete()
            ->cascadeOnUpdate();
            $table->string("picture_path");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_element_pictures');
    }
};
