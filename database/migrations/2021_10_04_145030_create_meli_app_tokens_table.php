<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMeliAppTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meli_app_tokens', function(Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('state');
            $table->longText('access_token');
            $table->datetime('access_token_expires_at');
            $table->string('refresh_token');
            $table->datetime('refresh_token_expires_at');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('meli_app_tokens');
    }
}
