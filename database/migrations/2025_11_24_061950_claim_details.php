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
        Schema::create('claim_details', function (Blueprint $table) {
            $table->string('claim_details_reference_number', 55)->primary();
            $table->text('description');
            $table->text('location_lost');
            $table->date('date_lost');
            $table->time('time_lost');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('claim_details');
    }
};
