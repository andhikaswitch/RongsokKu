<!DOCTYPE html>
<html lang="id" data-tema="{{ session('tema', 'terang') }}" class="scroll-pt-20">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('judul', 'Jual Rongsok Jadi Mudah') &middot; RongsokKu</title>
    <meta name="description" content="@yield('deskripsi', 'RongsokKu menghubungkan warga dengan pengepul terdekat. Harga transparan, barang dijemput ke rumah.')">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen font-sans">
    {{-- Lompat ke konten: bantuan navigasi keyboard. --}}
    <a href="#konten"
       class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50
              focus:rounded-lg focus:bg-merk-600 focus:px-4 focus:py-2 focus:text-sm
              focus:font-semibold focus:text-white">
        Lompat ke konten utama
    </a>

    {{ $slot }}
</body>
</html>
