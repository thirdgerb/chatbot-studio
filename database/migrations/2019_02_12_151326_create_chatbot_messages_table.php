<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Commune\Chatbot\Laravel\Database\TableSchema;

class CreateChatbotMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chatbot_messages', function (Blueprint $table) {
            $table->bigIncrements('id');

            TableSchema::id('reply_to_id', $table);
            TableSchema::scope($table);

            $table->binary('message_data');
            $table->boolean('from_user');
            $table->string('message_type', 200)->default('');

            TableSchema::scopeIndex($table);
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
        Schema::dropIfExists('chatbot_messages');
    }
}
