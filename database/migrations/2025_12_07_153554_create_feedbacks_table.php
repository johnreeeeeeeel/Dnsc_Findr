<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->string('feedback_id', 10)->primary();
            $table->string('user_id', 20);
            $table->boolean('is_anonymous')->default(false);

            // Service
            $table->enum('service_used', ['Claim Lost', 'Report Found']);

            // Ratings & Message
            $table->unsignedTinyInteger('rating_system')->check('rating_system BETWEEN 1 AND 5');

            $table->text('message_system');

            $table->unsignedTinyInteger('rating_service')->check('rating_service BETWEEN 1 AND 5');

            $table->text('message_service');

            $table->timestamp('submitted_at')->useCurrent();

            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('feedbacks');
    }
};
