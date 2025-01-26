<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('collaborative_projects', function (Blueprint $table) {
            $table->string('required_roles')->after('description')->nullable(false);
            $table->string('skills_required')->after('required_roles')->nullable(false);
            $table->date('deadline')->after('skills_required')->nullable(); // Allowing NULL to avoid invalid date error
            $table->decimal('budget', 10, 2)->after('deadline')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('collaborative_projects', function (Blueprint $table) {
            $table->dropColumn([
                'required_roles',
                'skills_required',
                'deadline',
                'budget'
            ]);
        });
    }
};
