@push('kepala')
    {{-- Satu-satunya halaman yang memakai JavaScript: peta interaktif dan GPS. --}}
    @vite('resources/js/atur-lokasi.js')
@endpush

<x-layouts.panel judul="Atur Titik Lokasi" keterangan="Tentukan titik tepat rumah atau lapak Anda di peta">
    @section('judul', 'Atur Titik Lokasi')

    <div class="grid max-w-5xl gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <x-kartu judul="Geser pin ke lokasi Anda"
                     keterangan="Klik peta atau seret pin merah. Geser peta dengan menahan klik, perbesar dengan scroll atau cubit.">
                <div id="peta-lokasi" data-lat="{{ $lat }}" data-lng="{{ $lng }}"
                     class="z-0 h-[26rem] w-full rounded-2xl ring-1 ring-slate-200 dark:ring-slate-800"></div>
            </x-kartu>
        </div>

        <div class="space-y-6">
            <x-kartu judul="Titik terpilih">
                <p class="text-sm text-slate-500 dark:text-slate-400">Koordinat</p>
                <p id="teks-koordinat" class="mt-1 select-all font-mono text-sm font-semibold text-slate-900 dark:text-white">
                    {{ number_format($lat, 6, '.', '') }}, {{ number_format($lng, 6, '.', '') }}
                </p>

                <x-tombol type="button" id="tombol-gps" variant="halus" penuh ikon="pin" class="mt-4">Gunakan lokasi saya (GPS)</x-tombol>
                <p id="status-gps" class="mt-2 text-xs text-slate-500 dark:text-slate-400" aria-live="polite"></p>

                <form method="POST" action="{{ route($awalan.'.lokasi.simpan') }}" class="mt-4">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="latitude" name="latitude" value="{{ $lat }}">
                    <input type="hidden" id="longitude" name="longitude" value="{{ $lng }}">
                    <x-tombol type="submit" variant="primer" penuh ikon="cek">Simpan titik ini</x-tombol>
                </form>

                <x-tombol :href="route($awalan)" variant="hantu" ukuran="kecil" penuh ikon="panah-kiri" class="mt-2">Kembali ke akun</x-tombol>
            </x-kartu>

            <div class="rounded-2xl bg-sky-50 p-4 text-sm text-sky-900 dark:bg-sky-500/10 dark:text-sky-100">
                Titik ini dipakai untuk menghitung jarak antara warga dan pengepul, dan untuk tombol petunjuk arah
                Google Maps. Jika Anda mengganti kelurahan di halaman akun, titik kembali ke pusat kelurahan.
            </div>
        </div>
    </div>
</x-layouts.panel>
