<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Drop the old 'role' column first
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });

        // Recreate the 'role' column with only 'admin', 'scrapSeller', 'artist'
        // No default value is set
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'scrapSeller', 'artist']);
        });
    }

    public function down()
    {
        // Reverse the changes if needed
        // (Recreate 'role' column the way it was before if you want)
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });

        // Example of re-adding an old definition if you had it
        // (Feel free to adjust as necessary)
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'artist', 'scrapSeller', 'general'])
                  ->default('general');
        });
    }
};
