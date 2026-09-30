<?php

namespace App\Http\Controllers\Auth;

use App\Enums\PeranPengguna;
use App\Enums\StatusVerifikasi;
use App\Http\Controllers\Controller;
use App\Models\ProfilPengepul;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function formLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $kredensial = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [], [
            'email' => 'alamat email',
            'password' => 'kata sandi',
        ]);

        if (! Auth::attempt($kredensial, $request->boolean('ingat'))) {
            throw ValidationException::withMessages([
                'email' => 'Email atau kata sandi yang Anda masukkan salah.',
            ]);
        }

        if (! $request->user()->aktif) {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => 'Akun Anda sedang dinonaktifkan. Silakan hubungi admin RongsokKu.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()
            ->intended(route($request->user()->peran->beranda()))
            ->with('sukses', 'Selamat datang kembali, '.$request->user()->name.'!');
    }

    public function formRegister(Request $request): View
    {
        $peran = $request->query('peran') === 'pengepul' ? 'pengepul' : 'warga';
        $kelurahan = $this->daftarKelurahan();

        return view('auth.register', compact('peran', 'kelurahan'));
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'telepon' => ['required', 'string', 'max:20', 'regex:/^08[0-9]{8,12}$/'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'peran' => ['required', 'in:warga,pengepul'],
            'wilayah_id' => ['required', 'exists:wilayah,id'],
            'alamat_detail' => ['required', 'string', 'max:255'],
            'nama_usaha' => ['required_if:peran,pengepul', 'nullable', 'string', 'max:100'],
        ], [
            'telepon.regex' => 'Nomor telepon harus diawali 08 dan terdiri dari 10 sampai 14 angka.',
            'nama_usaha.required_if' => 'Nama usaha wajib diisi untuk pendaftaran pengepul.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ], [
            'name' => 'nama lengkap',
            'email' => 'alamat email',
            'telepon' => 'nomor telepon',
            'password' => 'kata sandi',
            'wilayah_id' => 'kelurahan',
            'alamat_detail' => 'alamat lengkap',
            'nama_usaha' => 'nama usaha',
        ]);

        $pengguna = DB::transaction(function () use ($data) {
            $wilayah = Wilayah::find($data['wilayah_id']);

            $pengguna = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'peran' => $data['peran'],
                'telepon' => $data['telepon'],
                'wilayah_id' => $wilayah->id,
                'alamat_detail' => $data['alamat_detail'],
                // Koordinat diambil dari titik kelurahan karena tanpa JavaScript
                // kita tidak bisa membaca GPS peramban.
                'latitude' => $wilayah->latitude,
                'longitude' => $wilayah->longitude,
            ]);

            if ($data['peran'] === 'pengepul') {
                ProfilPengepul::create([
                    'user_id' => $pengguna->id,
                    'nama_usaha' => $data['nama_usaha'],
                    'slug' => Str::slug($data['nama_usaha']).'-'.Str::lower(Str::random(4)),
                    'status_verifikasi' => StatusVerifikasi::Draf,
                ]);
            }

            return $pengguna;
        });

        Auth::login($pengguna);
        $request->session()->regenerate();

        $pesan = $pengguna->peran === PeranPengguna::Pengepul
            ? 'Akun dibuat. Lengkapi berkas verifikasi agar lapak Anda bisa tampil di pencarian.'
            : 'Akun berhasil dibuat. Selamat datang di RongsokKu!';

        return redirect()->route($pengguna->peran->beranda())->with('sukses', $pesan);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('beranda')->with('sukses', 'Anda telah keluar. Sampai jumpa!');
    }

    /** Daftar kelurahan dikelompokkan per kecamatan untuk <optgroup>. */
    private function daftarKelurahan(): \Illuminate\Support\Collection
    {
        return Wilayah::kelurahan()
            ->with('induk')
            ->orderBy('induk_id')
            ->orderBy('nama')
            ->get()
            ->groupBy(fn ($w) => $w->induk?->nama ?? 'Lainnya');
    }
}
