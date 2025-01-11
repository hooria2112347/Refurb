<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCustomRequestIdToCustomRequestCommentsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('custom_request_comments', function (Blueprint $table) {
            // Assuming custom_requests table exists with an 'id' primary key
            $table->unsignedBigInteger('custom_request_id')->after('id');

            // Optionally, add a foreign key constraint if you want referential integrity
            $table->foreign('custom_request_id')
                  ->references('id')
                  ->on('custom_requests')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('custom_request_comments', function (Blueprint $table) {
            // Drop the foreign key constraint and column if rolling back
            $table->dropForeign(['custom_request_id']);
            $table->dropColumn('custom_request_id');
        });
    }
}
