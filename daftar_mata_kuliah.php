<?php
require 'koneksi.php';

function e($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }


$teks = [
    'tambah' => 'Mata kuliah berhasil ditambahkan.',
    'edit'   => 'Mata kuliah berhasil diperbarui.',
    'hapus'  => 'Mata kuliah berhasil dihapus.',
];
$k = $_GET['pesan'] ?? '';
$pesanSukses = (is_string($k) && isset($teks[$k])) ? $teks[$k] : '';


$q = (isset($_GET['q']) && is_string($_GET['q'])) ? trim($_GET['q']) : '';
$sql = 'SELECT id, kode, nama, dosen, semester FROM mata_kuliah';
$tipe = '';
$par = [];
if ($q !== '') {
    $like = '%' . addcslashes($q, '%_\\') . '%';
    $sql .= ' WHERE kode LIKE ? OR nama LIKE ?';
    $tipe = 'ss';                       // dua tanda ? => dua tipe
    $par = [$like, $like];
}
$sql .= ' ORDER BY nama';

$stmt = $conn->prepare($sql);
if ($par) {
    $stmt->bind_param($tipe, ...$par);
}
$stmt->execute();
$hasil = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mata Kuliah</title>
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

    <h1>Mata kuliah</h1>
    <?php if ($pesanSukses !== ''): ?>
        <p class="pesan sukses" role="status"><?= e($pesanSukses) ?></p>
    <?php endif; ?>

    <div class="toolbar">
        <span>Menampilkan <?= count($hasil) ?> data.</span>
        <a class="btn btn-primary" href="tambah_mata_kuliah.php">Tambah mata kuliah</a>
    </div>

    <form method="GET" action="daftar_mata_kuliah.php" class="cari">
        <input type="text" name="q" placeholder="Cari kode atau nama" value="<?= e($q) ?>">
        <button type="submit" class="btn btn-primary">Cari</button>
        <a class="btn" href="daftar_mata_kuliah.php">Reset</a>
    </form>

    <?php if (!$hasil): ?>
        <p class="kosong"><?= $q !== '' ? 'Data tidak ditemukan.' : 'Belum ada data.' ?></p>
    <?php else: ?>
        <table>
            <thead>
                <tr><th>Mata kuliah</th><th>Dosen / semester</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                <?php foreach ($hasil as $r): ?>
                    <tr>
                        <td><?= e($r['kode']) ?> - <?= e($r['nama']) ?></td>
                        <td><?= e($r['dosen']) ?> / Semester <?= (int) $r['semester'] ?></td>
                        <td>
                            <a class="btn" href="detail_mata_kuliah.php?id=<?= (int) $r['id'] ?>">Detail</a>
                            <a class="btn" href="edit_mata_kuliah.php?id=<?= (int) $r['id'] ?>">Edit</a>
                            <a class="btn btn-danger" href="hapus_mata_kuliah.php?id=<?= (int) $r['id'] ?>">Hapus</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>
</html>
