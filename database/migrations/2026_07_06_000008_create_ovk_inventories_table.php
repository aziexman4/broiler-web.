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
        Schema::create('ovk_inventories', function (Blueprint $table) {
            $table->id();
            $table->date('record_date');
            $table->string('item_name');
            $table->enum('category', ['obat', 'vaksin', 'kimia']);
            $table->enum('transaction_type', ['in', 'out']);
            $table->decimal('quantity', 10, 2);
            $table->string('unit');
            $table->integer('total_price')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ovk_inventories');
    }
};
