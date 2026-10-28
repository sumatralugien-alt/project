<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('users')->where('role', 'Customer')->update(['role' => 'Student']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        /**
         * The removed role cannot be restored without reintroducing unsupported accounts.
         */
    }
};
