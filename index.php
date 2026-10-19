<?php
require 'koneksi.php';

function e($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }

$jumlah = [];
foreach (['mata_kuliah', 'materi', 'tugas'] as $tabel) {
    $jumlah[$tabel] = (int) $conn->query("SELECT COUNT(*) AS n FROM $tabel")->fetch_assoc()['n'];
}
$mkList = $conn->query('SELECT id, kode, nama FROM mata_kuliah ORDER BY nama')->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Learning Management System</title>
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

    <h1>Learning Management System</h1>
    <p>Pengelolaan mata kuliah, materi pembelajaran, dan tugas.</p>

    <div class="stat">
        <div>Mata kuliah<strong><?= $jumlah['mata_kuliah'] ?></strong></div>
        <div>Materi<strong><?= $jumlah['materi'] ?></strong></div>
        <div>Tugas<strong><?= $jumlah['tugas'] ?></strong></div>
    </div>

    <h2>Mata kuliah tersedia</h2>
    <?php if (!$mkList): ?>
        <p class="kosong">Belum ada mata kuliah.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($mkList as $m): ?>
                <li>
                    <a href="detail_mata_kuliah.php?id=<?= (int) $m['id'] ?>">
                        <?= e($m['kode']) ?> - <?= e($m['nama']) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
</body>
</html>
