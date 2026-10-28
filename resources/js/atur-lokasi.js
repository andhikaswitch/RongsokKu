// Pemilih titik lokasi: peta bisa digeser dan di-zoom, pin bisa diklik atau diseret,
// dan tombol GPS mengambil lokasi perangkat. Hanya dimuat di halaman "Atur titik lokasi".
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

const wadah = document.getElementById('peta-lokasi');

if (wadah) {
    const kolomLat = document.getElementById('latitude');
    const kolomLng = document.getElementById('longitude');
    const teksKoordinat = document.getElementById('teks-koordinat');
    const statusGps = document.getElementById('status-gps');
    const awal = L.latLng(Number(wadah.dataset.lat), Number(wadah.dataset.lng));

    const peta = L.map(wadah).setView(awal, 17);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; Kontributor OpenStreetMap',
    }).addTo(peta);

    const ikonPin = L.divIcon({
        className: '',
        html: '<svg viewBox="0 0 24 24" width="40" height="40" fill="#e11d48" stroke="#fff" stroke-width="1.5"><path d="M12 22s7-6.2 7-12a7 7 0 1 0-14 0c0 5.8 7 12 7 12z"/><circle cx="12" cy="10" r="2.5" fill="#fff"/></svg>',
        iconSize: [40, 40],
        iconAnchor: [20, 40], // ujung bawah pin = titik terpilih
    });
    const pin = L.marker(awal, { draggable: true, icon: ikonPin }).addTo(peta);

    // Satu tempat untuk memperbarui pin, kolom tersembunyi, dan teks koordinat.
    const pilih = (titik) => {
        pin.setLatLng(titik);
        kolomLat.value = titik.lat.toFixed(7);
        kolomLng.value = titik.lng.toFixed(7);
        teksKoordinat.textContent = `${titik.lat.toFixed(6)}, ${titik.lng.toFixed(6)}`;
    };

    peta.on('click', (e) => pilih(e.latlng));
    pin.on('dragend', () => pilih(pin.getLatLng()));

    document.getElementById('tombol-gps').addEventListener('click', () => {
        if (!navigator.geolocation) {
            statusGps.textContent = 'Peramban ini tidak mendukung GPS.';
            return;
        }

        statusGps.textContent = 'Mencari lokasi Anda…';
        navigator.geolocation.getCurrentPosition(
            ({ coords }) => {
                const titik = L.latLng(coords.latitude, coords.longitude);
                pilih(titik);
                peta.setView(titik, 18);
                statusGps.textContent = `Lokasi ditemukan (akurasi ± ${Math.round(coords.accuracy)} m). Geser pin bila belum pas.`;
            },
            () => {
                statusGps.textContent = 'Izin lokasi ditolak atau GPS tidak tersedia. Pilih titik langsung di peta.';
            },
            { enableHighAccuracy: true, timeout: 15000 },
        );
    });
}
