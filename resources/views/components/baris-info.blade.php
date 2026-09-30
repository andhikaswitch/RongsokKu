@props(['label'])

<div {{ $attributes->merge(['class' => 'flex items-start justify-between gap-4 py-2.5']) }}>
    <dt class="shrink-0 text-sm text-slate-500 dark:text-slate-400">{{ $label }}</dt>
    <dd class="min-w-0 text-right text-sm font-medium text-slate-900 dark:text-white">{{ $slot }}</dd>
</div>
