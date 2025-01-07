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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->string('contract_number', 255)->nullable();
            $table->string('title', 255);
            $table->integer('project_id')->unsigned()->nullable(false);
            $table->integer('application_id')->unsigned()->nullable();
            $table->integer('user_id')->unsigned()->nullable(false);
            $table->integer('status')->unsigned();
            $table->integer('currency_id')->nullable()->unsigned();
            $table->decimal('budget_sum', 20, 2);
            $table->date('deadline');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
