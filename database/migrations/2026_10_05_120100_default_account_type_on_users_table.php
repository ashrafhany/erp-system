<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->whereNull('account_type')->update(['account_type' => 'employee']);
        Schema::table('users', function (Blueprint $table) {
            $table->string('account_type', 50)->default('employee')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('account_type', 50)->nullable()->default(null)->change();
        });
    }
};
