@props(['status'])
@php
$map = [
    'baru' => ['Baru', 'info'], 'diproses' => ['Diproses', 'warning'], 'dihantar' => ['Dihantar', 'info'],
    'selesai' => ['Selesai', 'success'], 'batal' => ['Batal', 'danger'],
    'live' => ['Live', 'success'], 'draf' => ['Draf', 'muted'], 'dijeda' => ['Dijeda', 'warning'],
    'aktif' => ['Aktif', 'success'], 'habis' => ['Stok Habis', 'danger'], 'tersembunyi' => ['Tersembunyi', 'muted'],
    'digantung' => ['Digantung', 'danger'],
    'belum' => ['Belum bayar', 'warning'], 'dibayar' => ['Dibayar', 'success'], 'gagal' => ['Gagal', 'danger'],
];
[$label, $tone] = $map[$status] ?? [$status, 'muted'];
@endphp
<x-ui.badge :tone="$tone" dot>{{ $label }}</x-ui.badge>
