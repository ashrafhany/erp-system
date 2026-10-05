<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        $names = collect(['الإدارة', 'المحاسبة', 'الموارد البشرية', 'المبيعات', 'التسويق', 'تقنية المعلومات', 'خدمة العملاء'])
            ->merge(DB::table('employees')->distinct()->pluck('department'))
            ->filter()->unique();
        foreach ($names as $name) {
            DB::table('departments')->insert(['name' => $name, 'created_at' => now(), 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
