<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('application', function (Blueprint $table) {
            $table->integer('status_id')->change();
        });
    }

    public function down(): void
    {
        Schema::table('application', function (Blueprint $table) {
            $table->integer('status_id')->unsigned()->change();
        });
    }
};
