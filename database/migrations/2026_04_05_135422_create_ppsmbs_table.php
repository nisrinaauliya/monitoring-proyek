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
        Schema::create('ppsmbs', function (Blueprint $table) {
            $table->id();
            $table->string('no_ppsmb')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->string('dept');
            $table->string('model_aplikasi'); // Aplikasi Internal, Aplikasi Eksternal, Improvement IT System
            $table->string('nama_project');
            $table->year('tahun');
            $table->string('quartal');
            $table->string('jenis_permintaan'); // Sistem Baru, Modul Baru, Modifikasi Baru
            $table->text('uraian_permintaan');
            $table->string('project_leader')->nullable();
            $table->string('pic_ba')->nullable();
            $table->string('secondary_ba')->nullable();
            $table->decimal('tangible_benefit', 15, 2)->nullable();
            $table->text('intangible_benefit')->nullable();
            $table->string('file')->nullable();
            $table->string('status')->default('Verifikasi CMD/Dinov');
            $table->integer('progress')->default(0);
            $table->date('estimasi_mulai')->nullable();
            $table->date('estimasi_selesai')->nullable();
            $table->timestamp('revisi_at')->nullable(); // waktu ppsmb dikembalikan ke user untuk revisi
            $table->timestamps();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ppsmbs');
    }
};
