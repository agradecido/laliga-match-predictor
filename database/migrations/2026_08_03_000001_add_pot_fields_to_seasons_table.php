<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seasons', function (Blueprint $table) {
            $table->decimal('entry_fee', 8, 2)->nullable();
            $table->foreignId('winner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('payout_at')->nullable();
            $table->foreignId('payout_marked_by_id')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('seasons', function (Blueprint $table) {
            $table->dropConstrainedForeignId('winner_user_id');
            $table->dropConstrainedForeignId('payout_marked_by_id');
            $table->dropColumn(['entry_fee', 'payout_at']);
        });
    }
};
