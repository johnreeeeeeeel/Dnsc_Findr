<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('found_items', function (Blueprint $table) {
            $table->string('item_id', 8)->primary();
            $table->string('item_name', 100);
            $table->string('item_category', 50);
            $table->text('description');
            $table->string('photo_url', 255);
            $table->text('location_found');
            $table->date('date_found');
            $table->time('time_found');
            $table->string('status', 50)->default('Not Yet Claimed');

            $table->enum('post_status', ['pending', 'approved', 'rejected', 'archived'])
                  ->default('pending');

            $table->string('posted_by');
            $table->timestamps();

            $table->foreign('posted_by')
                  ->references('user_id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('found_items');
    }
};