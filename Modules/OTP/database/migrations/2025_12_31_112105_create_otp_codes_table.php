<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('otp_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('target');
            $table->enum('target_type', ['phone', 'email']);
            $table->string('otp_hash');
            $table->enum('purpose', ['login', 'register', 'reset_password']);
            $table->dateTime('expires_at');
            $table->dateTime('verified_at')->nullable();
            $table->timestamps();

            $table->index(['target', 'purpose']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otp_codes');
    }
};
