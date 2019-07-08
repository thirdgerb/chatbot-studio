<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Commune\Chatbot\Laravel\Database\TableSchema;

class CreateChatbotContextsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(TableSchema::CONTEXTS_TABLE, function (Blueprint $table) {
            $table->bigIncrements('id');
            TableSchema::id('context_id', $table);
            TableSchema::scope($table);

            $table->binary('content');
            $table->timestamps();

            $table->unique('context_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(TableSchema::CONTEXTS_TABLE);
    }
}
