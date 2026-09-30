<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Dilempar service ketika sebuah aksi melanggar aturan bisnis, misalnya
 * menerima permintaan yang sudah dibatalkan. Handler di bootstrap/app.php
 * mengubahnya menjadi redirect kembali dengan pesan galat, sehingga
 * controller tidak perlu menulis try/catch sendiri.
 */
class AksiTidakValid extends RuntimeException
{
}
