<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTimestampsToInvitationsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('invitations', function (Blueprint $table) {
        // $table->timestamps();  <-- This adds both created_at & updated_at
        // Instead, just add updated_at:

        $table->timestamp('updated_at')->nullable();
    });
}

public function down()
{
    Schema::table('invitations', function (Blueprint $table) {
        $table->dropColumn('updated_at');
    });
}

}
