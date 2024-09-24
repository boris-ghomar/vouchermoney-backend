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
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->decimal('amount');
            $table->enum('status', ['active', 'blocked', 'canceled', 'transferred', 'expired', 'used'])->default('active');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('used_by')->nullable();
            $table->dateTime("resolved_at")->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('customers')->cascadeOnDelete();
            $table->foreign('used_by')->references('id')->on('customers')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
