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
    Schema::create('institutes', function (Blueprint $table) {
        $table->string('institute_id', 8)->primary();
        $table->string('institute_code', 12)->unique();
        $table->string('institute_description', 255);
        $table->timestamps();
    });
}

public function down()
{
    Schema::dropIfExists('institutes');
}
};
