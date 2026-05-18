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
    if (!Schema::hasColumn('contracts', 'contact_id')) {
        Schema::table('contracts', function (Blueprint $table) {
            $table->unsignedBigInteger('contact_id')->nullable()->after('id');
        });
    }
}

public function down(): void
{
    Schema::table('contracts', function (Blueprint $table) {
        if (Schema::hasColumn('contracts', 'contact_id')) {
            $table->dropColumn('contact_id');
        }
    });
}
};
