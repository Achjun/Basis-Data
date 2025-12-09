<?php
include 'koneksi.php';

// Ambil ID Pembayaran dari URL
$no_bayar = $_GET['id'] ?? null;

if (!$no_bayar) {
    die("ID Pembayaran tidak ditemukan.");
}

// 1. QUERY UTAMA: Ambil data Pembayaran, Pasien, Dokter, Kasir
$query = "SELECT 
            byr.no_bayar, 
            byr.tgl_bayar, 
            byr.total_biaya_tindakan, 
            byr.total_biaya_obat, 
            byr.total_akhir,
            k.no_kunjungan,
            p.nama_pasien, 
            p.no_pasien,
            d.nama_dokter,
            u.nama_lengkap AS nama_kasir
          FROM pembayaran byr
          JOIN kunjungan k ON byr.no_kunjungan = k.no_kunjungan
          JOIN pasien p ON k.no_pasien = p.no_pasien
          JOIN dokter d ON k.no_dokter = d.no_dokter
          LEFT JOIN users u ON byr.id_petugas = u.id_user
          WHERE byr.no_bayar = ?";

$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "s", $no_bayar);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    die("Data pembayaran tidak ditemukan.");
}

// 2. QUERY KEDUA: Ambil detail obat berdasarkan No Kunjungan
// Kita cari resep yang terhubung ke kunjungan ini
$query_obat = "SELECT 
                o.nama_obat, 
                dr.jumlah, 
                o.harga_satuan,
                (dr.jumlah * o.harga_satuan) as subtotal_obat
               FROM resep r
               JOIN detail_resep dr ON r.no_resep = dr.no_resep
               JOIN obat o ON dr.kode_obat = o.kode_obat
               WHERE r.no_kunjungan = ?";

$stmt_obat = mysqli_prepare($koneksi, $query_obat);
mysqli_stmt_bind_param($stmt_obat, "i", $data['no_kunjungan']);
mysqli_stmt_execute($stmt_obat);
$result_obat = mysqli_stmt_get_result($stmt_obat);

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk Pembayaran - <?= $data['no_bayar'] ?></title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace; /* Font struk */
            font-size: 14px;
            color: #333;
            max-width: 80mm; /* Lebar kertas struk thermal standar */
            margin: 0 auto;
            padding: 10px;
            background: #fff;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px dashed #333;
            padding-bottom: 10px;
        }
        .header h2 { margin: 0; font-size: 18px; text-transform: uppercase; }
        .header p { margin: 2px 0; font-size: 12px; }
        
        .info-group {
            margin-bottom: 10px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 10px;
        }
        th { text-align: left; border-bottom: 1px solid #333; }
        td { padding: 4px 0; vertical-align: top; }
        .text-right { text-align: right; }
        
        .total-section {
            border-top: 2px dashed #333;
            padding-top: 5px;
            margin-top: 10px;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
        }
        
        /* Tombol print disembunyikan saat dicetak */
        @media print {
            .no-print { display: none; }
            body { margin: 0; padding: 0; }
        }
        
        .btn-print {
            background: #333; color: #fff; 
            border: none; padding: 10px 20px; 
            cursor: pointer; width: 100%; margin-bottom: 20px;
        }
    </style>
</head>
<body onload="window.print()">

    <button class="btn-print no-print" onclick="window.print()">Cetak Struk</button>

    <div class="header">
        <h2>KLINIK SEHAT APOTEK FARMA</h2>
        <p>Jl. Kesehatan No. 123, Jakarta</p>
        <p>Telp: (021) 555-7777</p>
    </div>

    <div class="info-group">
        <div class="info-row">
            <span>No. Bayar</span>
            <span>: <?= $data['no_bayar'] ?></span>
        </div>
        <div class="info-row">
            <span>Tanggal</span>
            <span>: <?= date('d/m/Y H:i', strtotime($data['tgl_bayar'])) ?></span>
        </div>
        <div class="info-row">
            <span>Pasien</span>
            <span>: <?= $data['nama_pasien'] ?></span>
        </div>
        <div class="info-row">
            <span>Dokter</span>
            <span>: <?= $data['nama_dokter'] ?></span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="45%">Item</th>
                <th width="15%" class="text-right">Jml</th>
                <th width="40%" class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php if($data['total_biaya_tindakan'] > 0): ?>
            <tr>
                <td>Jasa Medis & Tindakan</td>
                <td class="text-right">1</td>
                <td class="text-right"><?= number_format($data['total_biaya_tindakan'], 0, ',', '.') ?></td>
            </tr>
            <?php endif; ?>

            <?php 
            if (mysqli_num_rows($result_obat) > 0) {
                while($obat = mysqli_fetch_assoc($result_obat)): 
            ?>
            <tr>
                <td><?= $obat['nama_obat'] ?></td>
                <td class="text-right"><?= $obat['jumlah'] ?></td>
                <td class="text-right"><?= number_format($obat['subtotal_obat'], 0, ',', '.') ?></td>
            </tr>
            <?php 
                endwhile; 
            }
            ?>
        </tbody>
    </table>

    <div class="total-section">
        <div class="info-row">
            <b>Total Tagihan</b>
            <b>Rp <?= number_format($data['total_akhir'], 0, ',', '.') ?></b>
        </div>
        </div>

    <div class="footer">
        <p>Terima Kasih atas Kunjungan Anda</p>
        <p>Semoga Lekas Sembuh</p>
        <br>
        <p>Kasir: <?= $data['nama_kasir'] ?? '-' ?></p>
    </div>

</body>
</html>