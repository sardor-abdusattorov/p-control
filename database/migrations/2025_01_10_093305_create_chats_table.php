<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->unsigned()->notNull();
            $table->integer('receiver_id')->unsigned()->notNull();
            $table->string('name', 255)->notNull();
            $table->string('model_type')->notNull();
            $table->integer('model_id')->unsigned()->notNull();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('chats');
    }
};
