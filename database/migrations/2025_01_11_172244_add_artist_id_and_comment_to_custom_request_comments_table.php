<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddArtistIdAndCommentToCustomRequestCommentsTable extends Migration
{
    public function up()
    {
        Schema::table('custom_request_comments', function (Blueprint $table) {
            // Only add the 'artist_id' column if it doesn't exist
            if (!Schema::hasColumn('custom_request_comments', 'artist_id')) {
                $table->unsignedBigInteger('artist_id')->after('custom_request_id');
                // Add foreign key constraint for 'artist_id'
                $table->foreign('artist_id')
                      ->references('id')
                      ->on('users')
                      ->onDelete('cascade');
            }

            // Only add the 'comment' column if it doesn't exist
            if (!Schema::hasColumn('custom_request_comments', 'comment')) {
                $table->text('comment')->after('artist_id');
            }
        });
    }

    public function down()
    {
        Schema::table('custom_request_comments', function (Blueprint $table) {
            // Check if columns exist before attempting to drop them
            if (Schema::hasColumn('custom_request_comments', 'artist_id')) {
                $table->dropForeign(['artist_id']);
                $table->dropColumn('artist_id');
            }
            if (Schema::hasColumn('custom_request_comments', 'comment')) {
                $table->dropColumn('comment');
            }
        });
    }
}
