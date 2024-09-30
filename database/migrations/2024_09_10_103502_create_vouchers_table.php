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
        Schema::create('voucher_codes', function (Blueprint $table) {
            $table->string('code', 40)->primary();
        });

        Schema::create('vouchers', function (Blueprint $table) {
            $table->string('code')->primary();
            $table->foreignUlid("customer_id")->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount');
            $table->boolean("active")->default(true);
            $table->timestamps();
        });

        Schema::create('archived_vouchers', function (Blueprint $table) {
            $table->string('code')->primary();
            $table->decimal('amount');
            $table->boolean('state')->comment("1 if redeemed, 0 if expired");
            $table->timestamp("resolved_at")->useCurrent();
            $table->json("customer_data");
            $table->json("recipient_data")->nullable();
            $table->timestamps();
        });

        Schema::create('voucher_activity', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->enum('from_state', ["created", "active", "frozen"]);
            $table->enum('to_state', ["active", "frozen", "redeemed", "expired"]);
            $table->string("description")->nullable();
            $table->json("user_data")->nullable();
            $table->timestamp("time")->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voucher_activity');
        Schema::dropIfExists('archived_vouchers');
        Schema::dropIfExists('vouchers');
        Schema::dropIfExists('voucher_codes');
    }
};
