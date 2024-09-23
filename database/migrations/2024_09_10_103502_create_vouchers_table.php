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
            $table->string('code')->unique()->index();
            $table->decimal('amount');
            $table->enum('status', ['active', 'blocked', 'cancelled', 'transferred', 'expired'])->default('active');
            $table->unsignedBigInteger('used_by')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->dateTime("cancelled_at")->nullable();
            $table->dateTime("expired_at")->nullable();

            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('used_by')->references('id')->on('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
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
