@props([
    // Tiap opsi: ['label' => ..., 'href' => ..., 'aktif' => bool, 'jumlah' => ?int]
    'opsi' => [],
])

<nav {{ $attributes->merge(['class' => 'geser-rapi flex gap-2 overflow-x-auto']) }} aria-label="Saring">
    @foreach ($opsi as $o)
        <a href="{{ $o['href'] }}"
           @if ($o['aktif']) aria-current="page" @endif
           @class([
               'inline-flex shrink-0 items-center gap-2 rounded-xl px-3.5 py-2 text-sm font-semibold transition',
               'bg-merk-600 text-white shadow-sm' => $o['aktif'],
               'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50 dark:bg-slate-900 dark:text-slate-300 dark:ring-slate-800 dark:hover:bg-slate-800' => ! $o['aktif'],
           ])>
            {{ $o['label'] }}
            @if (isset($o['jumlah']) && $o['jumlah'] !== null)
                <span @class([
                    'rounded-md px-1.5 py-0.5 text-xs tabular-nums',
                    'bg-white/20' => $o['aktif'],
                    'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' => ! $o['aktif'],
                ])>{{ $o['jumlah'] }}</span>
            @endif
        </a>
    @endforeach
</nav>
