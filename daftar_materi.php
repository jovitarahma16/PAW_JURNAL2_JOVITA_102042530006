<?php
require 'koneksi.php';

function e($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }

$teks = [
    'tambah' => 'Materi berhasil ditambahkan.',
    'edit'   => 'Materi berhasil diperbarui.',
    'hapus'  => 'Materi berhasil dihapus.',
];
$k = $_GET['pesan'] ?? '';
$pesanSukses = (is_string($k) && isset($teks[$k])) ? $teks[$k] : '';

$q = (isset($_GET['q']) && is_string($_GET['q'])) ? trim($_GET['q']) : '';
$mk = filter_var($_GET['mk'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($mk === false) {
    $mk = 0;
}

$sql = 'SELECT m.id, m.judul, k.kode, k.nama AS nama_mk
        FROM materi m
        JOIN mata_kuliah k ON k.id = m.mata_kuliah_id
        WHERE 1=1';
$tipe = '';
$par = [];

if ($q !== '') {
    $sql .= ' AND m.judul LIKE ?';
    $tipe .= 's';
    $par[] = '%' . addcslashes($q, '%_\\') . '%';
}
if ($mk > 0) {
    $sql .= ' AND m.mata_kuliah_id = ?';
    $tipe .= 'i';
    $par[] = $mk;
}
$sql .= ' ORDER BY m.judul';

$stmt = $conn->prepare($sql);
if ($par) {
    $stmt->bind_param($tipe, ...$par);
}
$stmt->execute();
$hasil = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$mkList = $conn->query('SELECT id, kode, nama FROM mata_kuliah ORDER BY nama')->fetch_all(MYSQLI_ASSOC);
$filterAktif = ($q !== '' || $mk > 0);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Materi</title>
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
    <?php if ($pesanSukses !== ''): ?>
        <p class="pesan sukses" role="status"><?= e($pesanSukses) ?></p>
    <?php endif; ?>

    <div class="toolbar">
        <span>Menampilkan <?= count($hasil) ?> data.</span>
        <a class="btn btn-primary" href="tambah_materi.php">Tambah materi</a>
    </div>

    <form method="GET" action="daftar_materi.php" class="cari">
        <input type="text" name="q" placeholder="Cari judul" value="<?= e($q) ?>">
        <select name="mk">
            <option value="0">Semua mata kuliah</option>
            <?php foreach ($mkList as $m): ?>
                <option value="<?= (int) $m['id'] ?>" <?= $mk === (int) $m['id'] ? 'selected' : '' ?>>
                    <?= e($m['kode']) ?> - <?= e($m['nama']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary">Cari</button>
        <a class="btn" href="daftar_materi.php">Reset</a>
    </form>

    <?php if (!$hasil): ?>
        <p class="kosong"><?= $filterAktif ? 'Data tidak ditemukan.' : 'Belum ada data.' ?></p>
    <?php else: ?>
        <table>
            <thead>
                <tr><th>Judul</th><th>Mata kuliah</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                <?php foreach ($hasil as $r): ?>
                    <tr>
                        <td><?= e($r['judul']) ?></td>
                        <td><?= e($r['kode']) ?> - <?= e($r['nama_mk']) ?></td>
                        <td>
                            <a class="btn" href="detail_materi.php?id=<?= (int) $r['id'] ?>">Detail</a>
                            <a class="btn" href="edit_materi.php?id=<?= (int) $r['id'] ?>">Edit</a>
                            <a class="btn btn-danger" href="hapus_materi.php?id=<?= (int) $r['id'] ?>">Hapus</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>
</html>
