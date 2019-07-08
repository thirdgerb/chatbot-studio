<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Commune\Chatbot\Laravel\Database\TableSchema;

class CreateChatbotIntentMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chatbot_intent_messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            TableSchema::scope($table);
            $table->string('user_name', 200)->default('');
            $table->string('message_type', 200)->default('');
            $table->string('message_text', 5000)->default('');
            $table->string('matched_intent', 200)->default('');
            $table->string('matched_entities', 5000)->default('');
            $table->string('nlu_intents', 5000)->default('');
            $table->boolean('session_heard')->default(false);

            TableSchema::scopeIndex($table);
            $table->index(['matched_intent'], 'intent_idx');
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
        Schema::dropIfExists('chatbot_intent_messages');
    }
}
