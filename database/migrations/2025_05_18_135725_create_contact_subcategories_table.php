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
        Schema::create('contact_subcategories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title', 500)->nullable(false);
            $table->integer('category_id')->nullable(false);
            $table->tinyInteger('status')->default(1);
            $table->text('info')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_subcategories');
    }
};
