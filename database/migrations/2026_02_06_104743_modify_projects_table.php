<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->nullable()->after('id');
            $table->integer('sort')->default(0)->after('title');

            $table->dropColumn(['deadline', 'project_year', 'budget_sum', 'user_id']);

            $table->foreign('category_id')->references('id')->on('project_categories')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn(['category_id', 'sort']);

            $table->decimal('budget_sum', 20, 2)->nullable();
            $table->dateTime('project_year')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->dateTime('deadline')->nullable();
        });
    }
};
