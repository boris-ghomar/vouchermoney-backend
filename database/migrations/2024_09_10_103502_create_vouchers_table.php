<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Voucher\ArchivedVoucher;
use App\Models\Voucher\VoucherActivity;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('voucher_codes', function (Blueprint $table) {
            $table->string('code')->primary();
        });

        Schema::create('vouchers', function (Blueprint $table) {
            $table->ulid("id")->primary();
            $table->string('code')->unique();
            $table->decimal('amount')->unsigned();
            $table->boolean("active")->default(true);
            $table->foreignUlid("customer_id")->constrained()->cascadeOnDelete();
            $table->nullableUlidMorphs("creator");
            $table->timestamps();
        });

        Schema::create('archived_vouchers', function (Blueprint $table) {
            $table->ulid("id")->primary();
            $table->string('code')->unique();
            $table->decimal('amount')->unsigned();

            $table->enum("state", [ArchivedVoucher::STATE_EXPIRED, ArchivedVoucher::STATE_REDEEMED])
                ->default(ArchivedVoucher::STATE_REDEEMED);

            $table->json("customer_data");
            $table->json("creator_data")->nullable();
            $table->json("recipient_data")->nullable();

            $table->string("recipient_note")->nullable();

            $table->timestamp("resolved_at")->useCurrent();
            $table->timestamps();
        });

        Schema::create('voucher_activity', function (Blueprint $table) {
            $table->id();
            $table->string('code');

            $table->enum("state", [
                VoucherActivity::STATE_CREATED,
                VoucherActivity::STATE_FROZEN,
                VoucherActivity::STATE_ACTIVATED,
                VoucherActivity::STATE_REDEEMED,
                VoucherActivity::STATE_EXPIRED
            ]);

            $table->json("properties")->nullable();

            // Who make action
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
