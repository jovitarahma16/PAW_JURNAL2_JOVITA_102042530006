<?php
session_start();
require 'koneksi.php';

function e($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($id === false) {
    http_response_code(400);
    exit('ID tugas tidak valid.');
}

$stmt = $conn->prepare('SELECT id, judul FROM tugas WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$data) {
    http_response_code(404);
    exit('Tugas tidak ditemukan.');
}

$pesan = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        exit('Token keamanan tidak valid. Muat ulang halaman.');
    }

    $idPost = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($idPost !== $id) {
        http_response_code(400);
        exit('ID tidak sesuai.');
    }

    try {
        $stmt = $conn->prepare('DELETE FROM tugas WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $terhapus = $stmt->affected_rows === 1;
        $stmt->close();

        if ($terhapus) {
            unset($_SESSION['csrf_token']);
            header('Location: daftar_tugas.php?pesan=hapus');
            exit;
        }
        $pesan = 'Tugas tidak berhasil dihapus.';
    } catch (mysqli_sql_exception $ex) {
        $pesan = 'Tugas gagal dihapus karena kesalahan database.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hapus Tugas</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <nav>
        <a href="index.php">Beranda</a>
        <a href="daftar_mata_kuliah.php">Mata kuliah</a>
        <a href="daftar_tugas.php">Tugas</a>
        <a href="daftar_tugas.php">Tugas</a>
    </nav>
    <hr>

    <h1>Tugas</h1>

    <?php if ($pesan !== ''): ?>
        <p class="pesan error" role="alert"><?= e($pesan) ?></p>
    <?php endif; ?>

    <p>Hapus <strong><?= e($data['judul']) ?></strong>?</p>
    <p>Data baru dihapus setelah tombol konfirmasi dikirim.</p>

    <form method="POST" action="hapus_tugas.php?id=<?= (int) $data['id'] ?>" class="aksi">
        <input type="hidden" name="id" value="<?= (int) $data['id'] ?>">
        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
        <button type="submit" class="btn btn-danger">Ya, hapus data</button>
        <a class="btn" href="daftar_tugas.php">Batal</a>
    </form>
</div>
</body>
</html>
