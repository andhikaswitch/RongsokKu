<?php

namespace App\Services;

use App\Models\ProfilPengepul;
use App\Models\Wilayah;
use App\Support\Haversine;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PengepulSearchService
{
    /**
     * Cari pengepul terverifikasi, diurutkan menurut kriteria yang dipilih.
     * Jarak dihitung di dalam query agar pengurutan "terdekat" dikerjakan
     * database, bukan PHP.
     */
    public function cari(array $filter): LengthAwarePaginator
    {
        $titik = $this->titikAcuan($filter);

        $query = ProfilPengepul::query()
            ->sudahTerverifikasi()
            ->with(['user.wilayah.induk', 'harga.kategori']);

        if ($titik) {
            $query->join('users', 'users.id', '=', 'profil_pengepul.user_id')
                ->whereNotNull('users.latitude')
                ->select('profil_pengepul.*')
                ->selectRaw(
                    Haversine::sql('users.latitude', 'users.longitude', $titik['lat'], $titik['lng']).' as jarak'
                );
        }

        if (! empty($filter['q'])) {
            $query->where('profil_pengepul.nama_usaha', 'like', '%'.$filter['q'].'%');
        }

        if (! empty($filter['kategori'])) {
            $query->whereHas('harga', fn ($q) => $q
                ->where('kategori_sampah_id', $filter['kategori'])
                ->where('sedang_menerima', true));
        }

        if (! empty($filter['b3'])) {
            $query->where('profil_pengepul.izin_b3', true);
        }

        if (! empty($filter['buka'])) {
            $query->where('profil_pengepul.sedang_menerima', true);
        }

        match ($filter['urut'] ?? 'jarak') {
            'rating' => $query->orderByDesc('profil_pengepul.rating_rata')
                ->orderByDesc('profil_pengepul.jumlah_ulasan'),
            'transaksi' => $query->orderByDesc('profil_pengepul.total_transaksi'),
            'kepatuhan' => $query->orderByDesc('profil_pengepul.skor_kepatuhan_harga'),
            default => $titik
                ? $query->orderBy('jarak')
                : $query->orderByDesc('profil_pengepul.rating_rata'),
        };

        return $query->paginate(9)->withQueryString();
    }

    /**
     * Titik acuan perhitungan jarak: wilayah yang dipilih di filter,
     * atau lokasi pengguna yang sedang masuk.
     */
    private function titikAcuan(array $filter): ?array
    {
        if (! empty($filter['wilayah'])) {
            $w = Wilayah::find($filter['wilayah']);

            if ($w?->latitude) {
                return ['lat' => (float) $w->latitude, 'lng' => (float) $w->longitude];
            }
        }

        $pengguna = auth()->user();

        if ($pengguna?->latitude) {
            return ['lat' => (float) $pengguna->latitude, 'lng' => (float) $pengguna->longitude];
        }

        return null;
    }
}
