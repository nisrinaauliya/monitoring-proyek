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
        Schema::create('ppsmb_detail_pengerjaans', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ppsmb_id')
              ->constrained('ppsmbs')
              ->onDelete('cascade');

            $table->string('menu');

            $table->enum('penilaian', [
                'Low',
                'Medium',
                'High'
            ]);

            $table->decimal('mandays', 5, 2);

            $table->decimal('adjustment_mandays', 5, 2)
                ->nullable();

            $table->boolean('is_done')
                ->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ppsmb_detail_pengerjaans');
    }
};
