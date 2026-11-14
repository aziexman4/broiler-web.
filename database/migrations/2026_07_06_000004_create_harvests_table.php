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
        Schema::create('harvests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flock_id')->constrained()->cascadeOnDelete();
            $table->date('harvest_date');
            $table->unsignedInteger('total_chickens_sold');
            $table->decimal('total_weight_kg', 10, 2);
            $table->string('buyer_name');
            $table->decimal('chicken_price_per_kg', 12, 2)->default(0);
            $table->decimal('feed_price_per_kg', 12, 2)->default(0);
            $table->decimal('total_feed_cost', 15, 2)->default(0);
            $table->decimal('gross_revenue', 15, 2)->default(0);
            $table->decimal('net_profit', 15, 2)->default(0);
            $table->decimal('revenue', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('harvests');
    }
};
