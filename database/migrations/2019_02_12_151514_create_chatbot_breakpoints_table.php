<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Commune\Chatbot\Laravel\Database\TableSchema;

class CreateChatbotBreakpointsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(TableSchema::BREAKPOINTS_TABLE, function (Blueprint $table) {
            $table->bigIncrements('id');
            TableSchema::id('breakpoint_id', $table);
            TableSchema::scope($table);

            $table->binary('content');

            $table->unique('breakpoint_id');
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
        Schema::dropIfExists(TableSchema::BREAKPOINTS_TABLE);
    }
}
