<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('subacquirer_id')->nullable()->constrained('subacquirers');
        });

        $map = DB::table('subacquirers')->pluck('id', 'name');
        if ($map->isNotEmpty()) {
            foreach ($map as $name => $id) {
                DB::table('users')->where('subacquirer', $name)->update(['subacquirer_id' => $id]);
            }
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('subacquirer');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('subacquirer', ['SubadqA', 'SubadqB']);
            $table->dropConstrainedForeignId('subacquirer_id');
        });
    }
};