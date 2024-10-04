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
        Schema::create('transactions', function (Blueprint $table) {
            $table->ulid("id")->primary();
            $table->foreignUlid("customer_id")->constrained()->cascadeOnDelete();
            $table->decimal("amount");
            $table->string("description");
            $table->nullableUlidMorphs("model");
            $table->timestamps();
        });

        Schema::create('archived_transactions', function (Blueprint $table) {
            $table->ulid("id")->primary();
            $table->foreignUlid("customer_id")->constrained()->cascadeOnDelete();
            $table->decimal("amount");
            $table->string("description");
            $table->nullableUlidMorphs("model");
            $table->timestamp("archived_at")->useCurrent();
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('archived_transactions');
        Schema::dropIfExists('transactions');
    }
};
