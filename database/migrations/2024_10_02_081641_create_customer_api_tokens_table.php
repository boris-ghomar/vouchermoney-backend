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
        Schema::create('customer_api_tokens', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid("customer_id")->constrained()->cascadeOnDelete();

            $table->string('token', 64)->unique();
            $table->string('name');
            $table->timestampTz('last_used_at')->nullable();
            $table->timestampTz('expires_at')->nullable();
            $table->softDeletesTz();  // Using softDeletesTz for deleted_at with timezone
            $table->timestampsTz();   // Using timestampsTz for created_at and updated_at
        });

        Schema::create('customer_api_token_activities', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid("token_id")->constrained('customer_api_tokens');

            $table->string("action");
            $table->json("request");
            $table->json("response");
            $table->json("properties")->nullable();
            $table->timestampsTz();   // Using timestampsTz for created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_api_token_activities');
        Schema::dropIfExists('customer_api_tokens');
    }
};
