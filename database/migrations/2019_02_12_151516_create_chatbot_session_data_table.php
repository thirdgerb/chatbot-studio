<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Commune\Chatbot\Laravel\Database\TableSchema;

class CreateChatbotSessionDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(TableSchema::SESSION_DATA_TABLE, function (Blueprint $table) {
            $table->increments('id');

            TableSchema::id('session_data_id', $table);
            $table->string('session_data_type', 60)
                ->comment('session data type')
                ->default('');

            TableSchema::scope($table);

            $table->binary('content');

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
        Schema::dropIfExists(TableSchema::SESSION_DATA_TABLE);
    }
}
