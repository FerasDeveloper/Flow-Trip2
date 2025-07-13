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
        Schema::create('auth_requests', function (Blueprint $table) {
            $table->id();
            $table->longText('description');
            $table->string('location');
            $table->string('business_name');
            $table->foreignId('user_id')
            ->constrained('users')
            ->cascadeOnDelete()
            ->cascadeOnUpdate();
            $table->foreignId('owner_category_id')
            ->constrained('owner_categories')
            ->cascadeOnDelete()
            ->cascadeOnUpdate();
            $table->foreignId('country_id')
            ->constrained('countries')
            ->cascadeOnDelete()
            ->cascadeOnUpdate();
            $table->string('activity_name')->nullable();
            $table->string('accommodation_type')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auth_requests');
    }
};
