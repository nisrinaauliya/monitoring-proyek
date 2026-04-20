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
            $table->foreignId('ppsmb_id')->constrained('ppsmbs')->onDelete('cascade');
            $table->string('pemeriksa');
            $table->string('status');
            $table->text('catatan')->nullable();
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
