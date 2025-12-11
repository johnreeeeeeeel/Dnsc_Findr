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
    Schema::create('programs', function (Blueprint $table) {
        $table->string('program_id', 8)->primary();
        $table->string('program_code', 12)->unique();
        $table->string('program_description', 255);
        $table->string('institute_id', 8);
        $table->foreign('institute_id')->references('institute_id')->on('institutes')->onDelete('cascade');
        $table->timestamps();
    });
}

public function down()
{
    Schema::dropIfExists('programs');
}
};
