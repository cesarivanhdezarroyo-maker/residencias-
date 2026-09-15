<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Le inyectamos TUS columnas a la tabla de Laravel
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'nombres')) {
                $table->string('nombres')->nullable()->after('name');
            }
            if (!Schema::hasColumn('users', 'apellidos')) {
                $table->string('apellidos')->nullable()->after('nombres');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nombres', 'apellidos']);
        });
    }
};