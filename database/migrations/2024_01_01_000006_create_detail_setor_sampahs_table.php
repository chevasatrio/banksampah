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
        Schema::create('detail_setor_sampahs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaksi_setor_id')->constrained('transaksi_setors')->cascadeOnDelete();
            $table->foreignId('jenis_sampah_id')->constrained('jenis_sampahs');
            $table->decimal('berat_kg', 8, 2);
            $table->decimal('harga_saat_itu', 10, 2);
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_setor_sampahs');
    }
};
