<?php

namespace Database\Seeders;

use App\Models\Lencana;
use App\Models\PengaturanPlatform;
use Illuminate\Database\Seeder;

class PengaturanSeeder extends Seeder
{
    public function run(): void
    {
        $pengaturan = [
            ['tarif_komisi', '5', 'angka', 'keuangan', 'Tarif Komisi (%)',
                'Persentase komisi yang dipotong dari saldo pengepul setiap transaksi selesai.'],
            ['saldo_minimum', '10000', 'angka', 'keuangan', 'Saldo Minimum',
                'Pengepul dengan saldo di bawah nilai ini tidak dapat menerima permintaan baru.'],
            ['topup_minimum', '25000', 'angka', 'keuangan', 'Nominal Top-up Minimum', null],
            ['bank_nama', 'Bank BCA', 'teks', 'keuangan', 'Bank Tujuan Top-up', null],
            ['bank_rekening', '1234567890', 'teks', 'keuangan', 'Nomor Rekening', null],
            ['bank_atas_nama', 'RongsokKu Indonesia', 'teks', 'keuangan', 'Atas Nama', null],

            ['min_transaksi_indeks', '5', 'angka', 'harga', 'Minimum Transaksi untuk Indeks',
                'Indeks harga baru ditampilkan setelah jumlah transaksi mencapai angka ini.'],
            ['ambang_peringatan_harga', '15', 'angka', 'harga', 'Ambang Peringatan Harga (%)',
                'Selisih terhadap indeks yang memicu label peringatan pada harga pengepul.'],

            ['nama_platform', 'RongsokKu', 'teks', 'umum', 'Nama Platform', null],
            ['kontak_email', 'halo@rongsokku.id', 'teks', 'umum', 'Email Kontak', null],
            ['kontak_wa', '081234567890', 'teks', 'umum', 'Nomor WhatsApp Admin', null],
        ];

        foreach ($pengaturan as [$kunci, $nilai, $tipe, $grup, $label, $keterangan]) {
            PengaturanPlatform::updateOrCreate(
                ['kunci' => $kunci],
                compact('nilai', 'tipe', 'grup', 'label', 'keterangan')
            );
        }

        $lencana = [
            ['Pemula Hijau', 'pemula-hijau', '🌱', 'Menyelesaikan transaksi pertama.', 1, 'lime'],
            ['Penjaga Lingkungan', 'penjaga-lingkungan', '♻️', 'Mendaur ulang 25 kg rongsok.', 25, 'emerald'],
            ['Pahlawan Sampah', 'pahlawan-sampah', '🦸', 'Mendaur ulang 100 kg rongsok.', 100, 'sky'],
            ['Penjaga Bumi', 'penjaga-bumi', '🌍', 'Mendaur ulang 250 kg rongsok.', 250, 'violet'],
            ['Legenda Daur Ulang', 'legenda-daur-ulang', '🏆', 'Mendaur ulang 500 kg rongsok.', 500, 'amber'],
        ];

        foreach ($lencana as $i => [$nama, $slug, $ikon, $deskripsi, $syarat, $warna]) {
            Lencana::updateOrCreate(['slug' => $slug], [
                'nama' => $nama,
                'ikon' => $ikon,
                'deskripsi' => $deskripsi,
                'syarat_berat_kg' => $syarat,
                'warna' => $warna,
                'urutan' => ($i + 1) * 10,
            ]);
        }
    }
}
