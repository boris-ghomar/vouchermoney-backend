<?php

use App\Models\Customer;
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
        Schema::create('customers', function (Blueprint $table) {
            $table->ulid("id")->primary();
            $table->string('name')->unique();
            // PostgreSQL requires precision and scale for decimal fields
            $table->decimal('balance', 15, 2)->default(0);

            // Using string for cross-compatibility instead of enum
            $table->string("type");

            $table->softDeletes();
            $table->timestampsTz();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->ulid("id")->primary();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestampTz('email_verified_at')->nullable();
            $table->string('password');
            $table->foreignUlid("customer_id")->nullable()->constrained()->cascadeOnDelete();
            $table->string("timezone")->default(config("app.timezone"));
            $table->rememberToken();
            $table->softDeletes();
            $table->timestampsTz();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestampTz('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignUlid('user_id')->nullable()->index();

            // PostgreSQL has native support for IP address storage with the inet type
            $table->ipAddress()->nullable();

            $table->text('user_agent')->nullable();
            // PostgreSQL does not have LONGTEXT, text() works for large content
            $table->text('payload');
            // Consider using bigInteger for future-proofing timestamps
            $table->bigInteger('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
        Schema::dropIfExists('customers');
    }
};
