<?php

namespace App\Http\Controllers;

use App\Models\Ppsmb;
use App\Models\PpsmbHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PpsmbByItController extends Controller
{
    public function index()
    {
        $ppsmbs = Ppsmb::with('user')
            ->whereIn('status', [
                'Antrian Analisa BA IT',
                'Analisa BA IT',
                'Antrian Development',
                'Proses Development',
                'UAT',
                'Done',
            ])
            ->latest()
            ->get();

        return view('ppsmbbyit.index', compact('ppsmbs'));
    }

    public function show($id)
    {
        $ppsmb = Ppsmb::with(['user', 'histories', 'detailPengerjaan'])->findOrFail($id);
        return view('ppsmbbyit.show', compact('ppsmb'));
    }

    public function generateNoPpsmb(Request $request, $id)
    {
        $ppsmb = Ppsmb::findOrFail($id);

        // generate no PPSMB format: PPSMB/YYYYMM/XXXXX
        $count = Ppsmb::whereNotNull('no_ppsmb')->count() + 1;
        $noPpsmb = 'PPSMB/' . date('Ym') . '/' . str_pad($count, 5, '0', STR_PAD_LEFT);

        $ppsmb->update([
            'no_ppsmb' => $noPpsmb,
            'pic_ba'   => $request->pic_ba,
            'secondary_ba' => $request->secondary_ba,
            'status'   => 'Analisa BA IT',
        ]);

        PpsmbHistory::create([
            'ppsmb_id'  => $ppsmb->id,
            'pemeriksa' => Auth::user()->name,
            'status'    => 'Analisa BA IT',
            'catatan'   => 'No PPSMB digenerate, PIC BA ditentukan',
        ]);

        return redirect()->route('ppsmbbyit.show', $ppsmb->id)->with('success', 'No PPSMB berhasil digenerate.');
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required',
        ]);

        $ppsmb = Ppsmb::findOrFail($id);

        $ppsmb->update([
            'status'   => $request->status,
            'progress' => $request->progress ?? $ppsmb->progress,
            'estimasi_mulai'   => $request->estimasi_mulai ?? $ppsmb->estimasi_mulai,
            'estimasi_selesai' => $request->estimasi_selesai ?? $ppsmb->estimasi_selesai,
        ]);

        PpsmbHistory::create([
            'ppsmb_id'  => $ppsmb->id,
            'pemeriksa' => Auth::user()->name,
            'status'    => $request->status,
            'catatan'   => $request->catatan ?? '-',
        ]);

        return redirect()->route('ppsmbbyit.show', $ppsmb->id)->with('success', 'Status PPSMB berhasil diupdate.');
    }
}
