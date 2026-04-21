<?php

namespace App\Http\Controllers;

use App\Models\Ppsmb;
use App\Models\PpsmbHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PpsmbByCmdController extends Controller
{
    public function index()
    {
        $ppsmbs = Ppsmb::with('user')
            ->where('model_aplikasi', 'Aplikasi Internal MD')
            ->whereIn('status', ['Verifikasi CMD/Dinov', 'Edit By User - Verifikasi CMD/Dinov'])
            ->latest()
            ->get();

        return view('ppsmbbycmd.index', compact('ppsmbs'));
    }

    public function show($id)
    {
        $ppsmb = Ppsmb::with(['user', 'histories', 'detailPengerjaan'])->findOrFail($id);
        return view('ppsmbbycmd.show', compact('ppsmb'));
    }

    public function approve(Request $request, $id)
    {
        $ppsmb = Ppsmb::findOrFail($id);

        $ppsmb->update([
            'status' => 'Antrian Analisa BA IT',
        ]);

        PpsmbHistory::create([
            'ppsmb_id'  => $ppsmb->id,
            'pemeriksa' => Auth::user()->name,
            'status'    => 'Antrian Analisa BA IT',
            'catatan'   => null,
        ]);

        return redirect()->route('ppsmbbycmd')->with('success', 'PPSMB berhasil disetujui.');
    }

    public function revisi(Request $request, $id)
    {
        $request->validate([
            'catatan' => 'required',
        ]);

        $ppsmb = Ppsmb::findOrFail($id);

        $ppsmb->update([
            'status'    => 'Revisi User',
            'revisi_at' => now(),
        ]);

        PpsmbHistory::create([
            'ppsmb_id'  => $ppsmb->id,
            'pemeriksa' => Auth::user()->name,
            'status'    => 'Revisi User',
            'catatan'   => $request->catatan,
        ]);

        return redirect()->route('ppsmbbycmd')->with('success', 'PPSMB dikembalikan ke user untuk direvisi.');
    }
}
