<?php

namespace App\Http\Controllers;

use App\Models\Ppsmb;
use App\Models\PpsmbHistory;
use App\Models\User;
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
            ])
            ->latest()
            ->paginate(10);

        return view('ppsmbbyit.index', compact('ppsmbs'));
    }

    public function show($id)
    {
        $ppsmb = Ppsmb::with(['user', 'histories', 'detailPengerjaan'])->findOrFail($id);
        
        $user = Auth::user();

        //menentukan tim project
        $timProject = match($ppsmb->model_aplikasi) {
            'Aplikasi DMS, FLP, Wanda CE (Booking) & Wanda Chatbot' => 'eksternal',
            default => 'internal',
        };

        $isProjectLeader = $user->role === 'project_leader' && $user->tim === $timProject;
        $isBa            = $user->role === 'business_analyst' && ($user->name === $ppsmb->pic_ba || $user->name === $ppsmb->secondary_ba);
        $isDeveloper     = $user->role === 'developer' && $user->name === $ppsmb->developer;

        // list PIC BA sesuai tim model aplikasi
        $listBa = match($ppsmb->model_aplikasi) {
            'Aplikasi DMS, FLP, Wanda CE (Booking) & Wanda Chatbot'     => User::where('dept', 'IT')->where('role', 'business_analyst')->where('tim', 'eksternal')->get(),
            'Improvement IT System'                                     => User::where('dept', 'IT')->where('role', 'business_analyst')->get(),
            default                                                     => User::where('dept', 'IT')->where('role', 'business_analyst')->where('tim', 'internal')->get(),
        };

        // list Developer sesuai tim model aplikasi
        $listDeveloper = match($ppsmb->model_aplikasi) {
            'Aplikasi DMS, FLP, Wanda CE (Booking) & Wanda Chatbot'     => User::where('dept', 'IT')->where('role', 'developer')->where('tim', 'eksternal')->get(),
            'Improvement IT System'                                     => User::where('dept', 'IT')->where('role', 'developer')->get(),
            default                                                     => User::where('dept', 'IT')->where('role', 'developer')->where('tim', 'internal')->get(),
        };

        return view('ppsmbbyit.show', compact('ppsmb', 'isProjectLeader', 'isBa', 'isDeveloper', 'listBa', 'listDeveloper'));
    }

    // Step 1 - Project Leader: Generate No PPSMB + pilih PIC BA
    public function generateNoPpsmb(Request $request, $id)
    {
        if (Auth::user()->role !== 'project_leader') {
            abort(403, 'Hanya Project Leader yang bisa melakukan aksi ini.');
        }

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
            'catatan'   => null,
        ]);

        return redirect()->route('ppsmbbyit.show', $ppsmb->id)->with('success', 'No PPSMB berhasil digenerate.');
    }

    // Step 2 - BA: Input detail pengerjaan
    public function simpanDetail(Request $request, $id)
    {
        if (Auth::user()->role !== 'business_analyst') {
            abort(403, 'Hanya Business Analyst yang bisa melakukan aksi ini.');
        }

        $ppsmb = Ppsmb::findOrFail($id);

        $request->validate([
            'detail'                => 'required|array|min:1',
            'detail.*.menu'         => 'required|string',
            'detail.*.penilaian'    => 'required|in:Low,Medium,High',
            'detail.*.mandays'      => 'required|numeric|min:0',
        ]);

        foreach ($request->detail as $item) {
            $ppsmb->detailPengerjaan()->create([
                'menu'               => $item['menu'],
                'penilaian'          => $item['penilaian'],
                'mandays'            => $item['mandays'],
                'adjustment_mandays' => null,
            ]);
        }

        $ppsmb->update([
            'status' => 'Antrian Development',
        ]);

        PpsmbHistory::create([
            'ppsmb_id'  => $ppsmb->id,
            'pemeriksa' => Auth::user()->name,
            'status'    => 'Antrian Development',
            'catatan'   => null,
        ]);

        return redirect()->route('ppsmbbyit.show', $ppsmb->id)->with('success', 'Detail pengerjaan berhasil disimpan.');
    }

    // Step 3 - Project Leader: Pilih developer
    public function assignDeveloper(Request $request, $id)
    {
        if (Auth::user()->role !== 'project_leader') {
            abort(403, 'Hanya Project Leader yang bisa melakukan aksi ini.');
        }

        $ppsmb = Ppsmb::findOrFail($id);

        $request->validate([
            'developer' => 'required|string',
        ]);

        $ppsmb->update([
            'developer' => $request->developer,
            'status'    => 'Proses Development',
        ]);

        PpsmbHistory::create([
            'ppsmb_id'  => $ppsmb->id,
            'pemeriksa' => Auth::user()->name,
            'status'    => 'Proses Development',
            'catatan'   => null,
        ]);

        return redirect()->route('ppsmbbyit.show', $ppsmb->id)->with('success', 'Developer berhasil ditentukan.');
    }

    // Project Leader: Update Estimasi (bisa kapan saja)
    public function updateEstimasi(Request $request, $id)
    {
        if (Auth::user()->role !== 'project_leader') {
            abort(403, 'Hanya Project Leader yang bisa melakukan aksi ini.');
        }

        $ppsmb = Ppsmb::findOrFail($id);

        $ppsmb->update([
            'estimasi_mulai'   => $request->estimasi_mulai,
            'estimasi_selesai' => $request->estimasi_selesai,
        ]);

        return redirect()->route('ppsmbbyit.show', $ppsmb->id)->with('success', 'Estimasi berhasil diupdate.');
    }

    // Step 4 - Developer: Update progress
    public function updateProgress(Request $request, $id)
    {
        if (Auth::user()->role !== 'developer') {
            abort(403, 'Hanya Developer yang bisa melakukan aksi ini.');
        }

        $ppsmb = Ppsmb::findOrFail($id);

        $request->validate([
            'is_done'           => 'nullable|array',
            'adjustment_mandays' => 'nullable|array',
        ]);

        foreach ($ppsmb->detailPengerjaan as $detail) {
            $detail->update([
                'is_done'            => isset($request->is_done[$detail->id]),
                'adjustment_mandays' => $request->adjustment_mandays[$detail->id] ?? $detail->adjustment_mandays,
            ]);
        }

        $totalMandays = $ppsmb->detailPengerjaan->sum('mandays');
        $mandaysDone  = $ppsmb->detailPengerjaan->where('is_done', true)->sum('mandays');

        $progress = $totalMandays > 0
            ? round(($mandaysDone / $totalMandays) * 90, 2)
            : 0;

        $ppsmb->update(['progress' => $progress]);

        PpsmbHistory::create([
            'ppsmb_id'  => $ppsmb->id,
            'pemeriksa' => Auth::user()->name,
            'status'    => 'Proses Development',
            'catatan'   => null,
        ]);

        return redirect()->route('ppsmbbyit.show', $ppsmb->id)->with('success', 'Progress berhasil diupdate.');
    }

    // Developer: Lanjut ke UAT
    public function lanjutUat($id)
    {
        if (Auth::user()->role !== 'developer') {
            abort(403, 'Hanya Developer yang bisa melakukan aksi ini.');
        }

        $ppsmb = Ppsmb::findOrFail($id);

        $ppsmb->update([
            'status'   => 'UAT',
            'progress' => 90,
        ]);

        PpsmbHistory::create([
            'ppsmb_id'  => $ppsmb->id,
            'pemeriksa' => Auth::user()->name,
            'status'    => 'UAT',
            'catatan'   => null,
        ]);

        return redirect()->route('ppsmbbyit.show', $ppsmb->id)->with('success', 'Project berhasil masuk ke tahap UAT.');
    }

    // Step 5 - Developer: UAT
    public function updateUat(Request $request, $id)
    {
        if (Auth::user()->role !== 'developer') {
            abort(403, 'Hanya Developer yang bisa melakukan aksi ini.');
        }

        $ppsmb = Ppsmb::findOrFail($id);

        $request->validate([
            'aksi' => 'required|in:revisi,done',
        ]);

        if ($request->aksi === 'revisi') {
            $ppsmb->update([
                'status'   => 'Proses Development',
            ]);

            PpsmbHistory::create([
                'ppsmb_id'  => $ppsmb->id,
                'pemeriksa' => Auth::user()->name,
                'status'    => 'Proses Development',
                'catatan'   => null,
            ]);
        } else {
            $ppsmb->update([
                'status'   => 'Done (Live)',
                'progress' => 100,
            ]);

            PpsmbHistory::create([
                'ppsmb_id'  => $ppsmb->id,
                'pemeriksa' => Auth::user()->name,
                'status'    => 'Done (Live)',
                'catatan'   => null,
            ]);
        }

        return redirect()->route('ppsmbbyit.show', $ppsmb->id)->with('success', 'Status UAT berhasil diupdate.');
    }
}
