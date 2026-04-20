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
            $table->foreignId('ppsmb_id')->constrained('ppsmbs')->onDelete('cascade');
            $table->string('menu');
            $table->string('penilaian')->nullable();
            $table->decimal('mandays', 8, 2)->nullable();
            $table->decimal('adjustment_mandays', 8, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ppsmb_detail_pengerjaan');
    }
};
