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
        Schema::create('claims', function (Blueprint $table) {
            $table->string('claim_reference_number', 55)->primary();
            $table->string('item_id', 8); 
            $table->string('user_id', 20); 
            $table->string('claim_details_reference_number', 55);
            $table->string('claim_status', 20)->default('pending');
            $table->timestamp('request_date')->useCurrent();

            $table->foreign('item_id')->references('item_id')->on('found_items')->onDelete('cascade');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('claim_details_reference_number')->references('claim_details_reference_number')->on('claim_details')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('claims');
    }
};
