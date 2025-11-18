<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pixes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('transaction_id')->nullable();
            $table->unsignedInteger('status')->default(0)->comment(
                '0: PENDING, 1: PROCESSING, 2: CONFIRMED, 3: PAID, 4: CANCELLED, 5: FAILED'
            );
            $table->decimal('amount', 12, 2);
            $table->string('payer_name')->nullable();
            $table->string('payer_document')->nullable();
            $table->unsignedBigInteger('expires_at')->nullable();
            $table->string('idempotency')->unique();
            $table->timestamp('payment_date')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
            $table->index('transaction_id');
            $table->index('expires_at');
            $table->index('idempotency');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pixes');
    }
};
