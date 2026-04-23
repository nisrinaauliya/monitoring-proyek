<?php

namespace App\Http\Controllers;

use App\Models\Ppsmb;
use App\Models\PpsmbHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PpsmbByUserController extends Controller
{
    public function index()
    {
        $ppsmbs = Ppsmb::with('user')->latest()->paginate(10);
        return view('ppsmbbyuser.index', compact('ppsmbs'));
    }

    public function create()
    {
        return view('ppsmbbyuser.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_project'      => 'required',
            'model_aplikasi'    => 'required',
            'tahun'             => 'required',
            'quartal'           => 'required',
            'jenis_permintaan'  => 'required',
            'uraian_permintaan' => 'required',
            'file'              => 'required|file|mimes:pdf,doc,docx|mimetypes:application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document|max:10240',
        ]);

        // upload file
        $file = $request->file('file');
        $fileName = $file->getClientOriginalName();
        $filePath = $file->storeAs('ppsmb_files', $fileName, 'public');

        // project leader berdasarkan model aplikasi
        $tim = match($request->model_aplikasi) {
            'Aplikasi DMS, FLP, Wanda CE (Booking) & Wanda Chatbot' => 'eksternal',
            default => 'internal',
        };

        $projectLeader = User::where('dept', 'IT')
            ->where('role', 'project_leader')
            ->where('tim', $tim)
            ->value('name');

        $status = match($request->model_aplikasi) {
            'Improvement IT System' => 'Antrian Analisa BA IT',
            default                 => 'Verifikasi CMD/Dinov',
        };

        $ppsmb = Ppsmb::create([
            'user_id'           => Auth::id(),
            'dept'              => Auth::user()->dept,
            'model_aplikasi'    => $request->model_aplikasi,
            'nama_project'      => $request->nama_project,
            'tahun'             => $request->tahun,
            'quartal'           => $request->quartal,
            'jenis_permintaan'  => implode(', ', $request->jenis_permintaan),
            'uraian_permintaan' => $request->uraian_permintaan,
            'tangible_benefit'  => $request->tangible_benefit,
            'intangible_benefit'=> $request->intangible_benefit,
            'file'              => $filePath,
            'status'            => $status,
            'project_leader'    => $projectLeader,
        ]);

        PpsmbHistory::create([
            'ppsmb_id'  => $ppsmb->id,
            'pemeriksa' => Auth::user()->name,
            'status'    => 'Verifikasi CMD/Dinov',
            'catatan'   => null,
        ]);

        return redirect()->route('ppsmbbyuser')->with('success', 'PPSMB berhasil diajukan.');
    }

    public function checkUat()
    {
        $uatCount = Ppsmb::where('dept', Auth::user()->dept)
            ->where('status', 'UAT')
            ->count();

        $uatAging = Ppsmb::where('dept', Auth::user()->dept)
            ->where('status', 'UAT')
            ->where('updated_at', '<=', now()->subDays(10))
            ->exists();

        return response()->json([
            'blocked' => $uatCount >= 3 && $uatAging
        ]);
    }

    public function show($id)
    {
        $ppsmb = Ppsmb::with(['user', 'histories', 'detailPengerjaan'])->findOrFail($id);
        return view('ppsmbbyuser.show', compact('ppsmb'));
    }

    public function edit($id)
    {
        $ppsmb = Ppsmb::where('user_id', Auth::id())
                       ->where('status', 'Revisi User')
                       ->findOrFail($id);
        return view('ppsmbbyuser.edit', compact('ppsmb'));
    }

    public function update(Request $request, $id)
    {
        $ppsmb = Ppsmb::where('user_id', Auth::id())
                       ->where('status', 'Revisi User')
                       ->findOrFail($id);

        $request->validate([
            'uraian_permintaan' => 'required',
            'file'              => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ]);

        $filePath = $ppsmb->file;
        if ($request->hasFile('file')) {
            //hapus file lama
            Storage::disk('public')->delete($ppsmb->file);

            //upload file baru
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $filePath = $file->storeAs('ppsmb_files', $fileName, 'public');
        }

        $status = match($request->model_aplikasi) {
            'Improvement IT System' => 'Antrian Analisa BA IT',
            default                 => 'Edit By User - Verifikasi CMD/Dinov',
        };

        $ppsmb->update([
            'uraian_permintaan' => $request->uraian_permintaan,
            'tangible_benefit'  => $request->tangible_benefit,
            'intangible_benefit'=> $request->intangible_benefit,
            'file'              => $filePath,
            'status'            => $status,
            'revisi_at'         => null,
        ]);

        PpsmbHistory::create([
            'ppsmb_id'  => $ppsmb->id,
            'pemeriksa' => Auth::user()->name,
            'status'    => $status,
            'catatan'   => null,
        ]);

        return redirect()->route('ppsmbbyuser')->with('success', 'PPSMB berhasil direvisi dan diajukan kembali.');
    }
}
