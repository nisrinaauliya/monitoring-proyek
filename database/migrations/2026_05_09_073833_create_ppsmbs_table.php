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

            $table->string('no_ppsmb')->unique()->nullable();

            $table->foreignId('user_id')
                  ->constrained('users');

            $table->foreignId('dept_id')
                  ->constrained('departments');

            $table->enum('model_aplikasi', [
                'Aplikasi Internal MD',
                'Aplikasi DMS, FLP, Wanda CE (Booking) & Wanda Chatbot',
                'Improvement IT System'
            ]);

            $table->string('nama_project');

            $table->year('tahun');

            $table->enum('quartal', [
                'Q1',
                'Q2',
                'Q3',
                'Q4'
            ]);

            $table->enum('jenis_permintaan', [
                'Sistem Baru',
                'Modul Baru',
                'Modifikasi Baru'
            ]);

            $table->text('uraian_permintaan');

            $table->foreignId('project_leader')
                  ->nullable()
                  ->constrained('users');

            $table->foreignId('pic_ba')
                  ->nullable()
                  ->constrained('users');

            $table->foreignId('secondary_ba')
                  ->nullable()
                  ->constrained('users');

            $table->foreignId('developer')
                  ->nullable()
                  ->constrained('users');

            $table->decimal('tangible_benefit', 15, 2)
                  ->nullable();

            $table->text('intangible_benefit')
                  ->nullable();

            $table->string('file')
                  ->nullable();

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

            $table->date('estimasi_mulai')
                  ->nullable();

            $table->date('estimasi_selesai')
                  ->nullable();

            $table->timestamp('revisi_at')
                  ->nullable();
                  
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
