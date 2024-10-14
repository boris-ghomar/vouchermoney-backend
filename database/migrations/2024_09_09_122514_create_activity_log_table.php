<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActivityLogTable extends Migration
{
    public function up()
    {
        Schema::connection(config('activitylog.database_connection'))->create(config('activitylog.table_name'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('log_name')->nullable();
            $table->text('description');
            // Using nullableMorphs for polymorphic relation with unique index names for PostgreSQL
            $table->nullableMorphs('subject', 'subject_type_subject_id_index');
            $table->string('event')->nullable();
            $table->nullableMorphs('causer', 'causer_type_causer_id_index');
            $table->json('properties')->nullable(); // JSON data, PostgreSQL supports it natively
            $table->uuid('batch_uuid')->nullable(); // UUID for batching, PostgreSQL supports native UUID type
            $table->timestampsTz(); // Timezone-aware timestamps
            $table->index('log_name'); // Index on log_name for query efficiency
        });
    }

    public function down()
    {
        Schema::connection(config('activitylog.database_connection'))->dropIfExists(config('activitylog.table_name'));
    }
}
