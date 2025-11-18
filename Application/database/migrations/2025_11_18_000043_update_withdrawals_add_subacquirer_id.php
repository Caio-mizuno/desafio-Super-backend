<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('withdrawals', function (Blueprint $table) {
            $table->foreignId('subacquirer_id')->nullable()->constrained('subacquirers');
        });

        DB::table('withdrawals')
            ->join('users', 'withdrawals.user_id', '=', 'users.id')
            ->update(['withdrawals.subacquirer_id' => DB::raw('users.subacquirer_id')]);
    }

    public function down(): void
    {
        Schema::table('withdrawals', function (Blueprint $table) {
            $table->dropConstrainedForeignId('subacquirer_id');
        });
    }
};