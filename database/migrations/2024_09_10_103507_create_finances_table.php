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
        Schema::create('finances', function (Blueprint $table) {
            $table->ulid("id")->primary();
            $table->foreignUlid('customer_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount');
            $table->text('comment')->nullable();
            $table->timestamps();
        });

        Schema::create('archived_finances', function (Blueprint $table) {
            $table->ulid("id")->primary();
            $table->foreignUlid('customer_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount');
            $table->boolean('status');
            $table->text('request_comment')->nullable();
            $table->text('resolved_comment')->nullable();
            $table->json("resolver_data")->nullable();
            $table->timestamp("resolved_at")->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('archived_finances');
        Schema::dropIfExists('finances');
    }
};
