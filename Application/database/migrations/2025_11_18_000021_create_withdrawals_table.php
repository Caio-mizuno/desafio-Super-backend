<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('external_withdraw_id')->nullable();
            $table->string('transaction_id')->nullable();
            $table->enum('status', ['PENDING', 'PROCESSING', 'SUCCESS', 'DONE', 'FAILED', 'CANCELLED'])->default('PENDING');
            $table->decimal('amount', 12, 2);
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
            $table->index('external_withdraw_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('withdrawals');
    }
};

