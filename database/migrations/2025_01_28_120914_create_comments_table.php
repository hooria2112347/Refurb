<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->integer('product_id'); // Foreign key to products (matches products.product_id)
            $table->unsignedBigInteger('user_id'); // Foreign key to users
            $table->text('comment'); // The review/comment text
            $table->tinyInteger('rating')->unsigned(); // Rating (e.g., 1-5)
            $table->timestamps(); // created_at and updated_at
        
            // Foreign key constraints
            $table->foreign('product_id')->references('product_id')->on('products')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        
            // Indexes for faster queries
            $table->index(['product_id', 'user_id']);
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('comments');
    }
}
