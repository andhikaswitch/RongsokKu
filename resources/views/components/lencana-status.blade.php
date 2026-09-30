@props(['status'])

{{-- Menerima enum StatusPermintaan, StatusVerifikasi, atau StatusTopup. --}}
<x-lencana :kelas-kustom="$status->warna()" {{ $attributes }}>
    {{ $status->label() }}
</x-lencana>
