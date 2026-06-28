<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Presensi;
use App\Models\Siswa;
use Illuminate\Http\Request;

class PresensiController extends Controller
{
    public function index()
    {
        return response()->json(Presensi::with(['siswa', 'guru', 'jadwal'])->orderBy('tanggal', 'desc')->orderBy('jam', 'desc')->get());
    }

    public function show($id)
    {
        $presensi = Presensi::find($id);
        if (!$presensi) return response()->json(['message' => 'Not found'], 404);
        return response()->json($presensi);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_siswa' => 'required|integer|exists:siswa,id',
            'tanggal' => 'required|date',
            'status' => 'required|string|in:sakit,izin,alpa',
            'catatan' => 'nullable|string|max:255',
        ]);

        $siswa = Siswa::find($data['id_siswa']);
        if (! $siswa) {
            return response()->json(['message' => 'Siswa tidak ditemukan.'], 404);
        }

        $guru = Guru::where('is_wali_kelas', 1)
            ->where('kelas_wali', $siswa->kelas)
            ->first();

        $presensi = Presensi::updateOrCreate(
            [
                'id_siswa' => $siswa->id,
                'tanggal' => $data['tanggal'],
                'metode' => 'manual_report',
            ],
            [
                'id_jadwal' => null,
                'id_guru' => $guru?->id_guru,
                'kelas' => $siswa->kelas,
                'jam' => now()->format('H:i:s'),
                'status' => $data['status'],
                'catatan' => $data['catatan'] ?? null,
                'shift_name' => 'Input laporan manual',
                'shift_status' => 'no_schedule',
                'shift_type' => null,
                'shift_index' => null,
            ]
        );

        return response()->json($presensi->fresh(['siswa', 'guru', 'jadwal']), $presensi->wasRecentlyCreated ? 201 : 200);
    }

    public function update(Request $request, $id)
    {
        $presensi = Presensi::find($id);
        if (! $presensi) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $data = $request->validate([
            'status' => 'sometimes|string|max:30',
            'jam' => 'sometimes|string|max:10',
            'metode' => 'sometimes|string|max:30',
            'catatan' => 'nullable|string|max:255',
        ]);
        $presensi->update($data);

        return response()->json([
            'message' => 'Updated',
            'data' => $presensi->fresh(),
        ]);
    }

    public function destroy($id)
    {
        $presensi = Presensi::find($id);
        if (! $presensi) {
            return response()->json(['message' => 'Not found'], 404);
        }
        $presensi->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
