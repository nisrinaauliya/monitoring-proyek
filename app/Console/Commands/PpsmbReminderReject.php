<?php

namespace App\Console\Commands;

use App\Mail\PpsmbRejectMail;
use App\Mail\PpsmbReminderMail;
use App\Models\Ppsmb;
use App\Models\PpsmbHistory;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class PpsmbReminderReject extends Command
{
    // protected $signature = 'app:ppsmb-reminder-reject';
    protected $signature = 'app:ppsmb-reminder-reject {--hari=}';
    protected $description = 'Kirim reminder revisi PPSMB dan auto reject jika melebihi 30 hari';

    public function handle()
    {
        $ppsmbs = Ppsmb::with('user')
            ->where('status', 'Revisi User')
            ->whereNotNull('revisi_at')
            ->get();

        foreach ($ppsmbs as $ppsmb) {
            // $hariKe = (int) Carbon::parse($ppsmb->revisi_at)->diffInDays(now());

            $hariKe = $this->option('hari')
                        ? (int) $this->option('hari')
                        : (int) Carbon::parse($ppsmb->revisi_at)->diffInDays(now());
                        
            // auto reject di hari ke-30
            if ($hariKe >= 30) {
                $ppsmb->update(['status' => 'Rejected']);

                PpsmbHistory::create([
                    'ppsmb_id'  => $ppsmb->id,
                    'pemeriksa' => null,
                    'status'    => 'Rejected',
                    'catatan'   => 'PPSMB otomatis direject karena tidak direvisi dalam 30 hari',
                ]);

                Mail::to($ppsmb->user->email)->send(new PpsmbRejectMail($ppsmb));

                $this->info("PPSMB #{$ppsmb->id} direject otomatis.");

            // kirim reminder di hari ke-7, 14, 21
            } elseif (in_array($hariKe, [7, 14, 21])) {
                Mail::to($ppsmb->user->email)->send(new PpsmbReminderMail($ppsmb, $hariKe));

                PpsmbHistory::create([
                    'ppsmb_id'  => $ppsmb->id,
                    'pemeriksa' => null,
                    'status'    => 'Revisi User',
                    'catatan'   => "Reminder hari ke-{$hariKe}: PPSMB belum direvisi. Segera lakukan revisi sebelum hari ke-30 atau pengajuan akan otomatis direject.",
                ]);

                $this->info("Reminder hari ke-{$hariKe} dikirim untuk PPSMB #{$ppsmb->id}.");
            }

        }
        $this->info('Selesai.');
    }
}