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
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value'); // mediumText works in PostgreSQL as text
            $table->bigInteger('expiration'); // Using bigint for expiration values
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->bigInteger('expiration'); // Changed to bigint
        });

        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->text('payload'); // Changed longText to text for PostgreSQL compatibility
            $table->smallInteger('attempts'); // Replacing unsignedTinyInteger with smallInteger
            $table->timestampTz('reserved_at')->nullable(); // Changed to timestampTz
            $table->timestampTz('available_at');
            $table->timestampTz('created_at');
        });

        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->text('failed_job_ids'); // Changed longText to text for PostgreSQL compatibility
            $table->mediumText('options')->nullable(); // Using text
            $table->timestampTz('cancelled_at')->nullable(); // Changed to timestampTz
            $table->timestampTz('created_at');
            $table->timestampTz('finished_at')->nullable(); // Changed to timestampTz
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->text('payload'); // Changed longText to text
            $table->text('exception'); // Changed longText to text
            $table->timestampTz('failed_at')->useCurrent(); // Changed to timestampTz
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('cache');
        Schema::dropIfExists('cache_locks');
    }
};
