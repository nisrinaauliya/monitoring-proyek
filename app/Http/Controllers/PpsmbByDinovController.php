<?php

namespace App\Http\Controllers;

use App\Models\Ppsmb;
use App\Models\PpsmbHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PpsmbByDinovController extends Controller
{
    public function index()
    {
        $ppsmbs = Ppsmb::with('user')
            ->where('model_aplikasi', 'Aplikasi DMS, FLP, Wanda CE (Booking) & Wanda Chatbot')
            ->whereIn('status', ['Verifikasi CMD/Dinov', 'Edit By User - Verifikasi CMD/Dinov'])
            ->latest()
            ->paginate(10);

        return view('ppsmbbydinov.index', compact('ppsmbs'));
    }

    public function show($id)
    {
        $ppsmb = Ppsmb::with(['user', 'histories', 'detailPengerjaan'])->findOrFail($id);
        return view('ppsmbbydinov.show', compact('ppsmb'));
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

        return redirect()->route('ppsmbbydinov')->with('success', 'PPSMB berhasil disetujui.');
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

        return redirect()->route('ppsmbbydinov')->with('success', 'PPSMB dikembalikan ke user untuk direvisi.');
    }
}
