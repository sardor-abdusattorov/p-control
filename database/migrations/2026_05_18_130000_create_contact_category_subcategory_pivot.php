<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('contact_category_subcategory')) {
            Schema::create('contact_category_subcategory', function (Blueprint $table) {
                $table->unsignedInteger('category_id');
                $table->unsignedInteger('subcategory_id');
                $table->primary(['category_id', 'subcategory_id']);
                $table->index('subcategory_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_category_subcategory');
    }
};
