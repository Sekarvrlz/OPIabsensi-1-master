<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('guru')) {
            return;
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE guru MODIFY nip VARCHAR(50) NULL');
            DB::statement('ALTER TABLE guru MODIFY username VARCHAR(100) NULL');
            DB::statement('ALTER TABLE guru MODIFY password VARCHAR(255) NULL');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('guru') || DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::table('guru')->whereNull('nip')->update(['nip' => '']);
        DB::table('guru')->whereNull('username')->update(['username' => '']);
        DB::table('guru')->whereNull('password')->update(['password' => '']);

        DB::statement('ALTER TABLE guru MODIFY nip VARCHAR(50) NOT NULL');
        DB::statement('ALTER TABLE guru MODIFY username VARCHAR(100) NOT NULL');
        DB::statement('ALTER TABLE guru MODIFY password VARCHAR(255) NOT NULL');
    }
};