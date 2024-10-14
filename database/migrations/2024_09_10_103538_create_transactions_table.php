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
            // Specify precision and scale for the decimal column
            $table->decimal("amount", 15, 2); // Allow negative amounts for withdrawals
            $table->string("description")->nullable();
            $table->nullableUlidMorphs("transactionable", "transactions_transactionable_index");
            $table->timestampsTz(); // Use timestampsTz for timezone support
        });

        Schema::create('archived_transactions', function (Blueprint $table) {
            $table->ulid("id")->primary();
            $table->foreignUlid("customer_id")->constrained()->cascadeOnDelete();
            // Specify precision and scale for the decimal column
            $table->decimal("amount", 15, 2); // Allow negative amounts for withdrawals
            $table->string("description")->nullable();
            $table->nullableUlidMorphs("transactionable", "archived_transactions_transactionable_index");
            $table->timestampTz("archived_at")->useCurrent(); // Use timestampTz for timezone support
            $table->timestampsTz(); // Use timestampsTz for timezone support
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
