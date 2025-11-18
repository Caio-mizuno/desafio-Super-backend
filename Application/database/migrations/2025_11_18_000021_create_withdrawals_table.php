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
            $table->unsignedInteger('status')->default(0)->comment(
                '0: PENDING, 1: PROCESSING, 2: SUCCESS, 3: DONE, 4: FAILED, 5: CANCELLED'
            );
            $table->decimal('amount', 12, 2);
            $table->foreignId('bank_account_id')->constrained('bank_accounts')->cascadeOnDelete();
            $table->timestamps();
            $table->index('external_withdraw_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('withdrawals');
    }
};
