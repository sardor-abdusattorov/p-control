<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $indexName = collect(DB::select('SHOW INDEX FROM contacts'))
            ->first(fn($row) => $row->Column_name === 'email' && $row->Non_unique == 0)?->Key_name;

        if ($indexName) {
            Schema::table('contacts', function (Blueprint $table) use ($indexName) {
                $table->dropUnique($indexName);
            });
        }
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->unique('email');
        });
    }
};
