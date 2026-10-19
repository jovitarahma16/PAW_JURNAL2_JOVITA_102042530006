<?php
require 'koneksi.php';

function e($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
function ambil($k) { return (isset($_POST[$k]) && is_string($_POST[$k])) ? trim($_POST[$k]) : ''; }

$id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($id === false) {
    http_response_code(400);
    exit('ID tugas tidak valid.');
}

$stmt = $conn->prepare(
    'SELECT id, mata_kuliah_id, judul, instruksi, tenggat, status FROM tugas WHERE id = ?'
);
$stmt->bind_param('i', $id);
$stmt->execute();
$lama = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$lama) {
    http_response_code(404);
    exit('Tugas tidak ditemukan.');
}

$mata_kuliah_id = (string) $lama['mata_kuliah_id'];
$judul = $lama['judul'];
$instruksi = $lama['instruksi'];
$tenggat = $lama['tenggat'];
$status = $lama['status'];
$errors = [];
$info = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mata_kuliah_id = ambil('mata_kuliah_id');
    $judul = ambil('judul');
    $instruksi = ambil('instruksi');
    $tenggat = ambil('tenggat');
    $status = ambil('status');

    $mk = filter_var($mata_kuliah_id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $mkAda = false;
    if ($mk !== false) {
        $stmt = $conn->prepare('SELECT id FROM mata_kuliah WHERE id = ?');
        $stmt->bind_param('i', $mk);
        $stmt->execute();
        $mkAda = $stmt->get_result()->num_rows === 1;
        $stmt->close();
    }
    if (!$mkAda) {
        $errors[] = 'Pilih mata kuliah yang tersedia.';
    }

    if (mb_strlen($judul) < 3 || mb_strlen($judul) > 150) {
        $errors[] = 'Judul tugas harus 3-150 karakter.';
    }
    if (mb_strlen($instruksi) < 3 || mb_strlen($instruksi) > 2000) {
        $errors[] = 'Instruksi tugas harus 3-2000 karakter.';
    }

    $tglOk = preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $tenggat, $m)
        && checkdate((int) $m[2], (int) $m[3], (int) $m[1]);
    if (!$tglOk) {
        $errors[] = 'Tanggal tenggat tidak valid.';
    }

    if (!in_array($status, ['Draf', 'Aktif', 'Selesai'], true)) {
        $errors[] = 'Status harus Draf, Aktif, atau Selesai.';
    }

    if (!$errors) {
        // Bandingkan data baru dengan data lama.
        $berubah = $mk !== (int) $lama['mata_kuliah_id']
            || $judul !== $lama['judul']
            || $instruksi !== $lama['instruksi']
            || $tenggat !== $lama['tenggat']
            || $status !== $lama['status'];

        if (!$berubah) {
            $info = 'Tidak ada perubahan data.';
        } else {
            try {
                $stmt = $conn->prepare(
                    'UPDATE tugas
                     SET mata_kuliah_id = ?, judul = ?, instruksi = ?, tenggat = ?, status = ?
                     WHERE id = ?'
                );
                $stmt->bind_param('issssi', $mk, $judul, $instruksi, $tenggat, $status, $id);
                $stmt->execute();
                $stmt->close();

                header('Location: daftar_tugas.php?pesan=edit');
                exit;
            } catch (mysqli_sql_exception $ex) {
                $errors[] = 'Gagal memperbarui data.';
            }
        }
    }
}

$mkList = $conn->query('SELECT id, kode, nama FROM mata_kuliah ORDER BY nama')->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Tugas</title>
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

    <h1>Edit Tugas</h1>

    <?php if ($errors): ?>
        <div class="pesan error" role="alert">
            <ul><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>
    <?php if ($info !== ''): ?>
        <p class="pesan" role="status"><?= e($info) ?></p>
    <?php endif; ?>

    <form method="POST" action="edit_tugas.php?id=<?= (int) $id ?>" class="form" novalidate>
        <p>
            <label for="mata_kuliah_id">Mata kuliah</label><br>
            <select id="mata_kuliah_id" name="mata_kuliah_id">
                <option value="">Pilih Mata kuliah</option>
                <?php foreach ($mkList as $m): ?>
                    <option value="<?= (int) $m['id'] ?>" <?= $mata_kuliah_id === (string) $m['id'] ? 'selected' : '' ?>>
                        <?= e($m['kode']) ?> - <?= e($m['nama']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <label for="judul">Judul tugas</label><br>
            <input type="text" id="judul" name="judul" maxlength="150" value="<?= e($judul) ?>">
        </p>
        <p>
            <label for="instruksi">Instruksi tugas</label><br>
            <textarea id="instruksi" name="instruksi" rows="5"><?= e($instruksi) ?></textarea>
        </p>
        <p>
            <label for="tenggat">Tenggat</label><br>
            <input type="date" id="tenggat" name="tenggat" value="<?= e($tenggat) ?>">
        </p>
        <p>
            <label for="status">Status</label><br>
            <select id="status" name="status">
                <?php foreach (['Draf', 'Aktif', 'Selesai'] as $st): ?>
                    <option value="<?= e($st) ?>" <?= $status === $st ? 'selected' : '' ?>><?= e($st) ?></option>
                <?php endforeach; ?>
            </select>
        </p>
        <div class="aksi">
            <button type="submit" class="btn btn-primary">Simpan data</button>
            <a class="btn" href="daftar_tugas.php">Batal</a>
        </div>
    </form>
</div>
</body>
</html>
