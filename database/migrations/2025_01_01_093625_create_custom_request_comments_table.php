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
        // Drop it first if it exists to avoid "table already exists" errors
        Schema::dropIfExists('custom_request_comments');

        // Now create the table
        Schema::create('custom_request_comments', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            // Add any additional columns you need, for example:
            // $table->unsignedBigInteger('user_id');
            // $table->text('comment');
            // ...
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('custom_request_comments');
    }
};
