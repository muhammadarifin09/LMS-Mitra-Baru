<?php

namespace App\Http\Controllers;

use App\Models\Biodata;
use App\Models\M_User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Enrollment;
use App\Models\Certificate;


class ProfilController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $biodata = Biodata::where('user_id', $user->id)->first();

        // 🔹 Kursus diikuti
        $totalKursus = Enrollment::where('user_id', $user->id)->count();

        // 🔹 Sertifikat dimiliki
        $totalSertifikat = Certificate::where('user_id', $user->id)->count();

        // 🔹 Progress rata-rata
        $avgProgress = Enrollment::where('user_id', $user->id)
            ->avg('progress_percentage');

        // Biar rapi & aman
        $avgProgress = $avgProgress ? round($avgProgress) : 0;

        return view('profil.index', compact(
            'user',
            'biodata',
            'totalKursus',
            'totalSertifikat',
            'avgProgress'
        ));
    }


    public function edit()
    {
        $user = Auth::user();
        $biodata = Biodata::where('user_id', $user->id)->first();

        return view('profil.edit', compact('user', 'biodata'));
    }

    public function update(Request $request)
{
    $user = Auth::user();
    
    $request->validate([
        'nama_lengkap' => 'required|string|max:255',
        'username_sobat' => 'required|string|max:255',
        'kecamatan' => 'required|string|max:255',
        'desa' => 'required|string|max:255',
        'alamat' => 'required|string',
        'no_telepon' => 'required|string|max:20',

        // ===== KOLOM BARU =====
        'posisi' => 'nullable|string|max:100',
        'posisi_daftar' => 'nullable|string|max:100',
        'alamat_prov' => 'nullable|string|max:100',
        'alamat_kab' => 'nullable|string|max:100',
        'tempat_tanggal_lahir' => 'nullable|string|max:150',
        'jenis_kelamin' => 'nullable|string|max:20',
        'pendidikan' => 'nullable|string|max:100',
        'pekerjaan' => 'nullable|string|max:100',
        'deskripsi_pekerjaan_lain' => 'nullable|string',

        'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    $biodata = Biodata::where('user_id', $user->id)->first();
    
    if (!$biodata) {
        $biodata = new Biodata();
        $biodata->user_id = $user->id;
        $biodata->id_sobat = 'SOBAT-' . time() . '-' . rand(1000, 9999);
    }

    // ===== Data utama =====
    $biodata->nama_lengkap = $request->nama_lengkap;
    $biodata->username_sobat = $request->username_sobat;
    $biodata->kecamatan = $request->kecamatan;
    $biodata->desa = $request->desa;
    $biodata->alamat = $request->alamat;
    $biodata->no_telepon = $request->no_telepon;

    // ===== Data tambahan =====
    $biodata->posisi = $request->posisi;
    $biodata->alamat_prov = $request->alamat_prov;
    $biodata->alamat_kab = $request->alamat_kab;
    $biodata->tempat_tanggal_lahir = $request->tempat_tanggal_lahir;
    $biodata->jenis_kelamin = $request->jenis_kelamin;
    $biodata->pendidikan = $request->pendidikan;
    $biodata->pekerjaan = $request->pekerjaan;
    $biodata->deskripsi_pekerjaan_lain = $request->deskripsi_pekerjaan_lain;
    
    // Handle upload foto profil
    if ($request->hasFile('foto_profil')) {
        // Hapus foto lama jika ada
        if ($biodata->foto_profil) {
            $oldPhotoPath = str_replace('storage/', 'public/', $biodata->foto_profil);
            Storage::delete($oldPhotoPath);
        }
        
        // Simpan foto baru
        $path = $request->file('foto_profil')->store('foto_profil', 'public');
        $biodata->foto_profil = $path; // Simpan path relatif
    }

    $biodata->save();

    // Sinkronkan nama ke tabel users
    M_User::where('id', $biodata->user_id)
        ->update([
            'nama' => $request->nama_lengkap
        ]);

    return redirect()->route('profil.index')
        ->with('success', 'Profil berhasil diperbarui!');
}

    public function hapusFoto(Request $request)
{
    $user = Auth::user();
    $biodata = Biodata::where('user_id', $user->id)->first();

    if ($biodata && $biodata->foto_profil) {
        // Hapus file dari storage
        $photoPath = str_replace('storage/', 'public/', $biodata->foto_profil);
        if (Storage::exists($photoPath)) {
            Storage::delete($photoPath);
        }
        
        // Hapus path dari database
        $biodata->foto_profil = null;
        $biodata->save();
    }

    return redirect()->route('profil.index')
        ->with('success', 'Foto profil berhasil dihapus!');
}
}