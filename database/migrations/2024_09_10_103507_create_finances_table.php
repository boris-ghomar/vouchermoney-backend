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
            $table->decimal('amount');
            $table->text('request_comment')->nullable();
            $table->enum('status', ['pending', 'canceled', 'approved', 'rejected'])->default('pending');
            $table->text('approved_comment')->nullable();
            $table->unsignedBigInteger('resolved_by')->nullable();
            $table->foreignId('customer_id');
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
