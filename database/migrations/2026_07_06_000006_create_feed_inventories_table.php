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
        Schema::create('feed_inventories', function (Blueprint $table) {
            $table->id();
            $table->date('transaction_date');
            $table->string('feed_name');
            $table->enum('transaction_type', ['in', 'out']);
            $table->decimal('quantity_kg', 10, 2);
            $table->integer('price_per_kg')->nullable();
            $table->string('notes')->nullable();
            $table->foreignId('flock_id')->nullable()->constrained('flocks')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feed_inventories');
    }
};
