<x-layouts.panel :judul="$judul">
    <x-kartu>
        <x-kosong ikon="petir"
                  :judul="$judul.' sedang dibangun'"
                  pesan="Kerangka modul ini sudah disiapkan lengkap dengan rute, hak akses, dan tabel database. Isi halaman dikerjakan pada fase berikutnya.">
            <x-tombol :href="route(auth()->user()->peran->beranda())" variant="sekunder" ikon="panah-kiri">
                Kembali ke Dashboard
            </x-tombol>
        </x-kosong>

        <div class="mx-auto max-w-md rounded-xl bg-slate-50 p-4 text-center dark:bg-slate-800/50">
            <p class="text-xs uppercase tracking-wider text-slate-400">Penanggung jawab</p>
            <p class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $penanggungJawab }}</p>
        </div>
    </x-kartu>
</x-layouts.panel>
