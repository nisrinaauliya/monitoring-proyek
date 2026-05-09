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
        Schema::create('ppsmb_histories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ppsmb_id')
              ->constrained('ppsmbs')
              ->onDelete('cascade');

            $table->foreignId('pemeriksa')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->enum('status', [
                'Verifikasi CMD/Dinov',
                'Revisi User',
                'Edit by User - Verifikasi CMD/Dinov',
                'Antrian Analisa BA IT',
                'Analisa BA IT',
                'Antrian Development',
                'Proses Development',
                'UAT',
                'Done (Live)',
                'Rejected'
            ]);

            $table->decimal('progress', 5, 2)
                ->default(0.00);

            $table->text('catatan')
                ->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ppsmb_histories');
    }
};
