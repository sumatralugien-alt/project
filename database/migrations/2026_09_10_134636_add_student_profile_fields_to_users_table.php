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
        Schema::table('users', function (Blueprint $table) {
            $table->string('student_number')->nullable()->unique()->after('role');
            $table->string('phone')->nullable()->after('student_number');
            $table->string('program')->nullable()->after('phone');
            $table->unsignedSmallInteger('year_level')->nullable()->after('program');
            $table->string('status')->default('Active')->after('year_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['student_number', 'phone', 'program', 'year_level', 'status']);
        });
    }
};
