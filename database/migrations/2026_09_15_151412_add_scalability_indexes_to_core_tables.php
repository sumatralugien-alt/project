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
        Schema::table('users', function (Blueprint $table): void {
            $table->index(['role', 'status'], 'users_role_status_index');
            $table->index(['role', 'created_at'], 'users_role_created_at_index');
        });

        Schema::table('events', function (Blueprint $table): void {
            $table->index(['status', 'starts_at'], 'events_status_starts_at_index');
        });

        Schema::table('event_registrations', function (Blueprint $table): void {
            $table->index(['event_id', 'status'], 'event_registrations_event_status_index');
            $table->index(['status', 'registered_at'], 'event_registrations_status_registered_at_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex('users_role_status_index');
            $table->dropIndex('users_role_created_at_index');
        });

        Schema::table('events', function (Blueprint $table): void {
            $table->dropIndex('events_status_starts_at_index');
        });

        Schema::table('event_registrations', function (Blueprint $table): void {
            $table->dropIndex('event_registrations_event_status_index');
            $table->dropIndex('event_registrations_status_registered_at_index');
        });
    }
};
