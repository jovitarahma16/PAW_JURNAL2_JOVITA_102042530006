<?php
require 'koneksi.php';

function e($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }

$id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($id === false) {
    http_response_code(400);
    exit('ID mata kuliah tidak valid.');
}

$stmt = $conn->prepare('SELECT id, kode, nama, dosen, semester FROM mata_kuliah WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$mk = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$mk) {
    http_response_code(404);
    exit('Mata kuliah tidak ditemukan.');
}

$stmt = $conn->prepare('SELECT id, judul FROM materi WHERE mata_kuliah_id = ? ORDER BY judul');
$stmt->bind_param('i', $id);
$stmt->execute();
$materi = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$stmt = $conn->prepare('SELECT id, judul, status FROM tugas WHERE mata_kuliah_id = ? ORDER BY judul');
$stmt->bind_param('i', $id);
$stmt->execute();
$tugas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Mata Kuliah</title>
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

    <h1>Detail mata kuliah</h1>
    <dl class="detail">
        <dt>Kode</dt><dd><?= e($mk['kode']) ?></dd>
        <dt>Nama</dt><dd><?= e($mk['nama']) ?></dd>
        <dt>Dosen</dt><dd><?= e($mk['dosen']) ?></dd>
        <dt>Semester</dt><dd><?= (int) $mk['semester'] ?></dd>
    </dl>
    <div class="aksi">
        <a class="btn btn-primary" href="edit_mata_kuliah.php?id=<?= (int) $mk['id'] ?>">Edit data</a>
        <a class="btn" href="daftar_mata_kuliah.php">Kembali</a>
    </div>

    <h2>Materi</h2>
    <?php if (!$materi): ?>
        <p class="kosong">Belum ada materi.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($materi as $r): ?>
                <li><a href="detail_materi.php?id=<?= (int) $r['id'] ?>"><?= e($r['judul']) ?></a></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <h2>Tugas</h2>
    <?php if (!$tugas): ?>
        <p class="kosong">Belum ada tugas.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($tugas as $r): ?>
                <li>
                    <a href="detail_tugas.php?id=<?= (int) $r['id'] ?>"><?= e($r['judul']) ?></a>
                    (<?= e($r['status']) ?>)
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
</body>
</html>
