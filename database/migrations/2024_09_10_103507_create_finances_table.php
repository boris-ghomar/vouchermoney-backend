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
            $table->decimal('amount'); // Allow negative amounts for withdrawals
            $table->foreignUlid('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('requester_id')->constrained("users")->cascadeOnDelete();
            $table->text('requester_comment')->nullable();
            $table->timestampsTz(); // Creates 'created_at' and 'updated_at' columns
        });

        Schema::create('archived_finances', function (Blueprint $table) {
            $table->ulid("id")->primary();
            $table->decimal('amount'); // Allow negative amounts for withdrawals
            $table->boolean('status')->comment("1 - approved, 0 - rejected");

            $table->foreignUlid('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('requester_id')->constrained("users")->cascadeOnDelete();
            $table->foreignUlid("resolver_id")->constrained("users")->cascadeOnDelete();

            $table->text('requester_comment')->nullable();
            $table->text('resolver_comment')->nullable();

            $table->timestampTz("resolved_at")->useCurrent(); // Use timestampTz for timezone-aware timestamps
            $table->timestampsTz(); // Creates 'created_at' and 'updated_at' columns
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
