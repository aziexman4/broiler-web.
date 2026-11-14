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
        Schema::table('flocks', function (Blueprint $table) {
            $table->decimal('stock_pre_starter_kg', 10, 2)->default(0)->after('status');
            $table->decimal('stock_starter_kg', 10, 2)->default(0)->after('stock_pre_starter_kg');
            $table->decimal('stock_finisher_kg', 10, 2)->default(0)->after('stock_starter_kg');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('flocks', function (Blueprint $table) {
            $table->dropColumn(['stock_pre_starter_kg', 'stock_starter_kg', 'stock_finisher_kg']);
        });
    }
};
