<?php
include 'koneksi.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Data Pasien</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>


<div id="wrapper" class="d-flex">

    <div class="bg-dark border-right" id="sidebar-wrapper">
        <div class="sidebar-heading text-white p-3 fs-4">Resepsionis</div>

        <div class="list-group list-group-flush">
            <a href="dashboard_resepsionis.php" class="list-group-item list-group-item-action bg-dark text-white">
                <i class="fa fa-users"></i> Dashboard
            </a>
            <a href="pasien_list.php" class="list-group-item list-group-item-action bg-dark text-white">
                <i class="fa fa-users"></i> Data Pasien
            </a>
        

            <a href="pasien_tambah.php" class="list-group-item list-group-item-action bg-dark text-white">
                <i class="fa fa-user-plus"></i> Tambah Pasien
            </a>

            <a href="kunjungan_tambah.php" class="list-group-item list-group-item-action bg-dark text-white">
                <i class="fa fa-calendar-plus"></i> Jadwal Kunjungan
            </a>

            <a href="kunjungan_list.php" class="list-group-item list-group-item-action bg-dark text-white">
                <i class="fa fa-calendar"></i> Data Kunjungan
            </a>

            <a href="../login.php?logout=true" class="list-group-item list-group-item-action bg-dark text-white">
                <i class="fa fa-right-from-bracket"></i> Logout
            </a>
        </div>
    </div>

<div class="container mt-4">

    <h3>Data Pasien</h3>

    <form method="GET" class="row g-3 mb-3">
        <div class="col-md-4">
            <input type="text" name="cari" class="form-control" 
                   placeholder="Cari Nama..." value="<?= $_GET['cari'] ?? '' ?>">
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary w-100">Cari</button>
        </div>
    </form>

    <?php
    $keyword = $koneksi->real_escape_string($_GET['cari'] ?? ''); // Sanitasi input
    
    // Menggunakan Prepared Statement untuk keamanan, meskipun dengan LIKE
    if ($keyword != "") {
        $query = "SELECT * FROM pasien WHERE nama_pasien LIKE ?";
        $stmt = $koneksi->prepare($query);
        $search_param = "%" . $keyword . "%";
        $stmt->bind_param("s", $search_param);
    } else {
        $query = "SELECT * FROM pasien";
        $stmt = $koneksi->prepare($query);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    ?>

<table class="table table-bordered table-striped">
    <thead class="table-dark">
        <tr>
            <th>Nama</th>
            <th>Alamat</th>
            <th>Tgl_lahir</th>
            <th>No Telp</th>
            <th>Aksi</th>
        </tr>
    </thead>

    <tbody>
    <?php while ($p = $result->fetch_assoc()): ?>
        <tr>

            <td><?= $p['nama_pasien'] ?></td>
            <td><?= $p['alamat'] ?></td>
            <td><?= $p['tgl_lahir'] ?></td>
            <td><?= $p['no_telepon'] ?></td>

            <td>
                <a href="kunjungan_tambah.php?id=<?= $p['no_pasien'] ?>" 
               class="btn btn-success btn-sm">
               Buat Kunjungan
            </a>
            </td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>

<?php $stmt->close(); ?>

</div>

</body>
</html>
</body>
</html>
