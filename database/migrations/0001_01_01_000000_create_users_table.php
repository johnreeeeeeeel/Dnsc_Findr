    <?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up(): void
        {
            Schema::create('users', function (Blueprint $table) {
                $table->string('user_id', 20)->primary();

                // Name fields
                $table->string('lastname')->nullable();
                $table->string('firstname')->nullable();
                $table->string('middlename')->nullable();

                // Personal info
                $table->string('sex', 10)->nullable();
                $table->date('dob')->nullable();

                // User type / role
                $table->enum('usertype', ['Admin', 'Instructor', 'Staff', 'Student'])->default('Student');

                // Additional info
                $table->string('institute')->nullable();
                $table->string('program')->nullable();

                // User account info
                $table->string('username')->unique();
                $table->string('email')->unique();
                $table->string('password')->nullable();

                $table->timestamp('email_verified_at')->nullable();
                $table->rememberToken();
                $table->timestamps();
            });

            Schema::create('password_reset_tokens', function (Blueprint $table) {
                $table->string('email')->primary();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });

            Schema::create('sessions', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->string('user_id')->nullable()->index(); // string instead of FK
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->longText('payload');
                $table->integer('last_activity')->index();
            });
        }

        public function down(): void
        {
            Schema::dropIfExists('users');
            Schema::dropIfExists('password_reset_tokens');
            Schema::dropIfExists('sessions');
        }
    };
