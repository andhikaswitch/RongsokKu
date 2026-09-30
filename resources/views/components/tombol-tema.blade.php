{{--
    Pergantian tema dikerjakan server: form POST menyimpan pilihan ke session,
    lalu atribut data-tema di <html> ikut berubah. Tanpa JavaScript.
--}}
@php $gelap = session('tema') === 'gelap'; @endphp

<form method="POST" action="{{ route('tema.ganti') }}" class="contents">
    @csrf
    <input type="hidden" name="kembali" value="{{ request()->fullUrl() }}">
    <button type="submit"
            title="{{ $gelap ? 'Gunakan mode terang' : 'Gunakan mode gelap' }}"
            aria-label="{{ $gelap ? 'Gunakan mode terang' : 'Gunakan mode gelap' }}"
            {{ $attributes->merge(['class' =>
                'grid size-9 place-items-center rounded-xl text-slate-500 transition
                 hover:bg-slate-100 hover:text-slate-900
                 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white'
            ]) }}>
        <x-ikon :nama="$gelap ? 'matahari' : 'bulan'" ukuran="size-4.5" />
    </button>
</form>
