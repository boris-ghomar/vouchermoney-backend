<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Voucher\ArchivedVoucher;

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
            $table->foreignUlid("customer_id")->constrained()->cascadeOnDelete();
            $table->ulidMorphs("creator");
            $table->boolean("active")->default(true);
            $table->timestampsTz(); // Creates 'created_at' and 'updated_at' columns
        });

        Schema::create('archived_vouchers', function (Blueprint $table) {
            $table->ulid("id")->primary();
            $table->string('code')->unique();
            $table->decimal('amount')->unsigned();
            $table->foreignUlid("customer_id")->constrained()->cascadeOnDelete();
            $table->ulidMorphs("creator");
            $table->enum("state", [ArchivedVoucher::STATE_EXPIRED, ArchivedVoucher::STATE_REDEEMED])
                ->default(ArchivedVoucher::STATE_REDEEMED);
            $table->foreignUlid("recipient_id")->nullable()->constrained("customers")->cascadeOnDelete();
            $table->ulidMorphs("resolver");
            $table->text("note")->nullable();
            $table->timestampTz("resolved_at")->useCurrent(); // Use timestampTz for timezone-aware timestamps
            $table->timestampsTz(); // Creates 'created_at' and 'updated_at' columns
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('archived_vouchers');
        Schema::dropIfExists('vouchers');
        Schema::dropIfExists('voucher_codes');
    }
};
