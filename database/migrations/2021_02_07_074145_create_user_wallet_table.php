<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserWalletTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_wallet', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('sender_id')->unsigned();
            $table->foreign('sender_id')->references('id')->on('users');
            $table->bigInteger('receiver_id')->unsigned();
            $table->foreign('receiver_id')->references('id')->on('users');
            $table->integer('amount');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_wallet');
    }
}
