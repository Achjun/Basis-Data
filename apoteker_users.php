<?php
include 'koneksi.php';
// session_start();

// hanya admin
// if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'Admin') {
//     header("Location: login.php");
//     exit;
// }

$user_name = $_SESSION['nama_lengkap'];

// Proses tambah apoteker
$errors = [];
$success = '';
if (isset($_POST['tambah_apoteker'])) {
    $username = trim($_POST['username']);
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $password = trim($_POST['password']);
    $role = 'apoteker';

    if (empty($username))
        $errors['username'] = "Username wajib diisi.";
    if (empty($nama_lengkap))
        $errors['nama_lengkap'] = "Nama lengkap wajib diisi.";
    if (empty($password))
        $errors['password'] = "Password wajib diisi.";

    // cek username unik
    $cek = $koneksi->query("SELECT * FROM users WHERE username='$username'");
    if ($cek->num_rows > 0)
        $errors['username'] = "Username sudah digunakan.";

    if (empty($errors)) {
        $sql = "INSERT INTO users (username, nama_lengkap, password, role)
                VALUES ('$username', '$nama_lengkap', '$password', '$role')";
        if ($koneksi->query($sql)) {
            $success = "Apoteker berhasil ditambahkan!";
        } else {
            $errors['general'] = "Terjadi kesalahan: " . $koneksi->error;
        }
    }
}

// Ambil data user role apoteker
$result = $koneksi->query("SELECT * FROM users WHERE role='apoteker'");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Kelola Apoteker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="d-flex" id="wrapper">
        <?php include 'sidebar_admin.php'; ?>
        <div id="page-content-wrapper">
            <?php include 'navbar_admin.php'; ?>

            <div class="container-fluid py-4">
                <h1 class="mb-4 text-primary"><i class="fas fa-capsules"></i> Kelola Data Apoteker</h1>

                <?php if (isset($_GET['msg'])): ?>
                    <div id="alert-msg"
                        class="alert <?= $_GET['msg'] == 'hapus_sukses' ? 'alert-success' : 'alert-danger' ?>">
                        <?= $_GET['msg'] == 'hapus_sukses' ? 'Apoteker berhasil dihapus!' : 'Gagal menghapus apoteker!' ?>
                    </div>
                <?php endif; ?>

                <!-- Tombol Tambah Apoteker -->
                <button class="btn btn-primary mb-3" type="button" id="btnTambahApoteker">
                    + Tambah Apoteker
                </button>

                <!-- Form Tambah Apoteker -->
                <?php
                $form_display = !empty($errors) ? 'block' : 'none';
                ?>
                <div class="card mb-4" id="formTambahApoteker" style="display:<?= $form_display ?>;">
                    <div class="card-header">Tambah Apoteker Baru</div>
                    <div class="card-body">
                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success"><?= $success ?></div>
                        <?php endif; ?>
                        <?php if (!empty($errors['general'])): ?>
                            <div class="alert alert-danger"><?= $errors['general'] ?></div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label>Username</label>
                                <input type="text" name="username" class="form-control"
                                    value="<?= isset($username) ? $username : '' ?>">
                                <?php if (!empty($errors['username'])): ?>
                                    <small class="text-danger"><?= $errors['username'] ?></small>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label>Nama Lengkap</label>
                                <input type="text" name="nama_lengkap" class="form-control"
                                    value="<?= isset($nama_lengkap) ? $nama_lengkap : '' ?>">
                                <?php if (!empty($errors['nama_lengkap'])): ?>
                                    <small class="text-danger"><?= $errors['nama_lengkap'] ?></small>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label>Password</label>
                                <input type="password" name="password" class="form-control">
                                <?php if (!empty($errors['password'])): ?>
                                    <small class="text-danger"><?= $errors['password'] ?></small>
                                <?php endif; ?>
                            </div>

                            <button type="submit" name="tambah_apoteker" class="btn btn-success">Simpan</button>
                        </form>
                    </div>
                </div>

                <!-- Tabel Apoteker -->
                <table class="table table-striped table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Username</th>
                            <th>Nama Lengkap</th>
                            <th>Role</th>
                            <th width="180">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><?= $row['username']; ?></td>
                                <td><?= $row['nama_lengkap']; ?></td>
                                <td><?= $row['role']; ?></td>
                                <td>
                                    <a href="apoteker_edit.php?id=<?= $row['id_user']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                    <a href="apoteker_delete.php?id=<?= $row['id_user']; ?>" class="btn btn-danger btn-sm"
                                        onclick="return confirm('Yakin ingin hapus apoteker ini?')">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle form tambah apoteker
        document.getElementById('btnTambahApoteker').addEventListener('click', function () {
            const form = document.getElementById('formTambahApoteker');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        });

        // Toggle sidebar
        document.getElementById("sidebarToggle").addEventListener("click", () => {
            document.getElementById("wrapper").classList.toggle("toggled");
        });

        // Alert auto-hide
        const alertMsg = document.getElementById('alert-msg');
        if (alertMsg) {
            setTimeout(() => {
                alertMsg.style.display = 'none';
            }, 3000);
        }
    </script>
</body>

</html>
