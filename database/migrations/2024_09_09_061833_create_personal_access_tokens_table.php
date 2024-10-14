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
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            // Using the built-in morphs method, which will create a string type for tokenable_type and a string/char for tokenable_id
            $table->morphs('tokenable');

            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable(); // Postgres handles text type well
            $table->timestampTz('last_used_at')->nullable(); // Timezone-aware
            $table->timestampTz('expires_at')->nullable(); // Timezone-aware
            $table->timestampsTz(); // Timezone-aware timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_access_tokens');
    }
};
