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
            $table->string('token',64)->unique();
            $table->string('name');
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('customer_api_token_activities', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid("token_id");
            $table->string("action");
            $table->json("request");
            $table->json("response");
            $table->json("properties")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_api_tokens');
    }
};
