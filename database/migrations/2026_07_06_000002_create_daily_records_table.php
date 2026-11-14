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
        Schema::create('daily_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flock_id')->constrained()->cascadeOnDelete();
            $table->date('record_date');
            $table->unsignedInteger('mortality')->default(0);
            $table->unsignedInteger('culling')->default(0);
            $table->decimal('feed_pre_starter_kg', 8, 2)->default(0.00);
            $table->decimal('feed_starter_kg', 8, 2)->default(0.00);
            $table->decimal('feed_finisher_kg', 8, 2)->default(0.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_records');
    }
};
