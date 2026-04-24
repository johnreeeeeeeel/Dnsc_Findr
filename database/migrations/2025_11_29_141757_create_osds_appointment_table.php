<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('osds_appointments', function (Blueprint $table) {
            $table->string('appointment_reference_number', 55)->primary();
            $table->string('user_id', 20);
            $table->date('schedule_date')->index();
            $table->time('schedule_time');
            $table->string('purpose', 50); // 'Report item' or 'Claim item'
            $table->enum('status', ['Pending', 'Approved', 'Rejected', 'Rescheduled'])
                  ->default('Pending')
                  ->index();
            $table->text('response_message')->nullable();
            $table->date('reschedule_date')->nullable();
            $table->time('reschedule_time')->nullable();
            $table->timestamp('sent_at')->useCurrent();

            $table->foreign('user_id')
                  ->references('user_id')
                  ->on('users')
                  ->onDelete('cascade');

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('osds_appointments');
    }
};