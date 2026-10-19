<?php
require 'koneksi.php';

function e($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }

$id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($id === false) {
    http_response_code(400);
    exit('ID tugas tidak valid.');
}

$stmt = $conn->prepare(
    'SELECT t.id, t.judul, t.instruksi, t.tenggat, t.status, k.kode, k.nama AS nama_mk
     FROM tugas t
     JOIN mata_kuliah k ON k.id = t.mata_kuliah_id
     WHERE t.id = ?'
);
$stmt->bind_param('i', $id);
$stmt->execute();
$r = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$r) {
    http_response_code(404);
    exit('Tugas tidak ditemukan.');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Tugas</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <nav>
        <a href="index.php">Beranda</a>
        <a href="daftar_mata_kuliah.php">Mata kuliah</a>
        <a href="daftar_materi.php">Materi</a>
        <a href="daftar_tugas.php">Tugas</a>
    </nav>
    <hr>

    <h1>Tugas</h1>
    <h2><?= e($r['judul']) ?></h2>
    <dl class="detail">
        <dt>Mata kuliah</dt><dd><?= e($r['kode']) ?> - <?= e($r['nama_mk']) ?></dd>
        <dt>Judul tugas</dt><dd><?= e($r['judul']) ?></dd>
        <dt>Instruksi</dt><dd><?= nl2br(e($r['instruksi'])) ?></dd>
        <dt>Tenggat</dt><dd><?= e($r['tenggat']) ?></dd>
        <dt>Status</dt><dd><?= e($r['status']) ?></dd>
    </dl>
    <div class="aksi">
        <a class="btn btn-primary" href="edit_tugas.php?id=<?= (int) $r['id'] ?>">Edit data</a>
        <a class="btn" href="daftar_tugas.php">Kembali</a>
    </div>
</div>
</body>
</html>
