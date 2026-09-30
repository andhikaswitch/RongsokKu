<?php

namespace App\Http\Controllers;

use App\Models\ItemPermintaan;
use App\Models\PermintaanTopup;
use App\Models\ProfilPengepul;
use App\Models\Sengketa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Menyajikan berkas pribadi (KTP, bukti transfer, foto barang, bukti sengketa)
 * yang sengaja tidak disimpan di disk publik. Setiap berkas hanya bisa dibuka
 * oleh pihak yang berhak melihatnya.
 */
class BerkasController extends Controller
{
    public function fotoBarang(Request $request, ItemPermintaan $item): StreamedResponse
    {
        $this->authorize('view', $item->permintaan);

        return $this->sajikan($item->foto_barang);
    }

    public function buktiTopup(Request $request, PermintaanTopup $topup): StreamedResponse
    {
        $user = $request->user();
        abort_unless($user->isAdmin() || (int) $user->profilPengepul?->id === (int) $topup->profil_pengepul_id, 403);

        return $this->sajikan($topup->bukti_transfer);
    }

    public function ktp(Request $request, ProfilPengepul $pengepul): StreamedResponse
    {
        $user = $request->user();
        abort_unless($user->isAdmin() || (int) $user->profilPengepul?->id === (int) $pengepul->id, 403);

        return $this->sajikan($pengepul->foto_ktp);
    }

    public function buktiSengketa(Request $request, Sengketa $sengketa): StreamedResponse
    {
        $this->authorize('view', $sengketa->permintaan);

        return $this->sajikan($sengketa->bukti);
    }

    private function sajikan(?string $path): StreamedResponse
    {
        abort_if(! $path || ! Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path);
    }
}
