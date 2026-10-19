<?php
require 'koneksi.php';

function e($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }

$id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($id === false) {
    http_response_code(400);
    exit('ID materi tidak valid.');
}

$stmt = $conn->prepare(
    'SELECT m.id, m.judul, m.isi, m.tautan, k.kode, k.nama AS nama_mk
     FROM materi m
     JOIN mata_kuliah k ON k.id = m.mata_kuliah_id
     WHERE m.id = ?'
);
$stmt->bind_param('i', $id);
$stmt->execute();
$r = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$r) {
    http_response_code(404);
    exit('Materi tidak ditemukan.');
}

$tautanAman = preg_match('#^https?://#i', $r['tautan']) === 1;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Materi</title>
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

    <h1>Materi</h1>
    <h2><?= e($r['judul']) ?></h2>
    <dl class="detail">
        <dt>Mata kuliah</dt><dd><?= e($r['kode']) ?> - <?= e($r['nama_mk']) ?></dd>
        <dt>Judul materi</dt><dd><?= e($r['judul']) ?></dd>
        <dt>Isi materi</dt><dd><?= nl2br(e($r['isi'])) ?></dd>
        <dt>Tautan referensi</dt>
        <dd>
            <?php if ($tautanAman): ?>
                <a href="<?= e($r['tautan']) ?>" target="_blank" rel="noopener noreferrer"><?= e($r['tautan']) ?></a>
            <?php else: ?>-<?php endif; ?>
        </dd>
    </dl>
    <div class="aksi">
        <a class="btn btn-primary" href="edit_materi.php?id=<?= (int) $r['id'] ?>">Edit data</a>
        <a class="btn" href="daftar_materi.php">Kembali</a>
    </div>
</div>
</body>
</html>
