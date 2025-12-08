<?php
include 'koneksi.php';
// session_start();

// Ambil ID user dari URL
if (!isset($_GET['id'])) {
    header("Location: kasir_users.php");
    exit;
}

$id_user = $_GET['id'];
$errors = [];
$success = '';

// Ambil data user
$result = $koneksi->query("SELECT * FROM users WHERE id_user='$id_user'");
if ($result->num_rows == 0) {
    header("Location: kasir_users.php");
    exit;
}
$user = $result->fetch_assoc();

// Proses update
if (isset($_POST['update_kasir'])) {
    $username = trim($_POST['username']);
    $nama_lengkap = trim($_POST['nama_lengkap']);

    if (empty($username))
        $errors['username'] = "Username wajib diisi.";
    if (empty($nama_lengkap))
        $errors['nama_lengkap'] = "Nama lengkap wajib diisi.";

    // cek username unik kecuali user sendiri
    $cek = $koneksi->query("SELECT * FROM users WHERE username='$username' AND id_user != '$id_user'");
    if ($cek->num_rows > 0)
        $errors['username'] = "Username sudah digunakan.";

    if (empty($errors)) {
        $sql = "UPDATE users SET username='$username', nama_lengkap='$nama_lengkap' WHERE id_user='$id_user'";
        if ($koneksi->query($sql)) {
            $success = "Data admin berhasil diperbarui!";
            $user['username'] = $username;
            $user['nama_lengkap'] = $nama_lengkap;
        } else {
            $errors['general'] = "Terjadi kesalahan: " . $koneksi->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Edit Admin</title>
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
                <h1 class="mb-4 text-primary"><i class="fas fa-user-edit"></i> Edit Kasir</h1>
                <a href="kasir_users.php" class="btn btn-secondary mb-3">&laquo; Kembali</a>

                <!-- Card Form Edit -->
                <div class="card mb-4">
                    <div class="card-header">Form Edit Kasir</div>
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
                                    value="<?= isset($username) ? $username : $user['username'] ?>">
                                <?php if (!empty($errors['username'])): ?>
                                    <small class="text-danger"><?= $errors['username'] ?></small>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label>Nama Lengkap</label>
                                <input type="text" name="nama_lengkap" class="form-control"
                                    value="<?= isset($nama_lengkap) ? $nama_lengkap : $user['nama_lengkap'] ?>">
                                <?php if (!empty($errors['nama_lengkap'])): ?>
                                    <small class="text-danger"><?= $errors['nama_lengkap'] ?></small>
                                <?php endif; ?>
                            </div>

                            <button type="submit" name="update_kasir" class="btn btn-success">Simpan Perubahan</button>
                        </form>
                    </div>
                </div>

                <!-- Dummy Table -->
                <table class="table table-striped table-bordered" style="visibility:hidden;">
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
                        <tr>
                            <td colspan="5">&nbsp;</td>
                        </tr>
                    </tbody>
                </table>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>