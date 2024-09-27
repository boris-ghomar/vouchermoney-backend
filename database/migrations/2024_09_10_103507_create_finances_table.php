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
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount');
            $table->text('request_comment')->nullable();
            $table->timestamps();
        });

        Schema::create('archived_finances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount');
            $table->text('request_comment')->nullable();
            $table->enum('status', ['approved', 'rejected']);
            $table->text('resolved_comment')->nullable();
            $table->dateTime("expired_at")->nullable();
            $table->unsignedBigInteger('resolved_by')->nullable();

            $table->foreign('resolved_by')->references('id')->on('users')->nullOnDelete(); //approve or reject by
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finances');
    }
};
