<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('permissions')->where('guard_name', 'web')->update(['guard_name' => 'admin']);
        DB::table('roles')->where('guard_name', 'web')->update(['guard_name' => 'admin']);
        DB::table('roles')->where('name', 'super admin')->update(['name' => 'super-admin']);
        DB::table('users')->where('roles_name', 'super admin')->update(['roles_name' => 'super-admin']);
    }

    public function down(): void
    {
        // Keep existing role assignments and guard names intact.
    }
};
