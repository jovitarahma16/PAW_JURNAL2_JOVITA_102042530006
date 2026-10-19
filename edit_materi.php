<?php
require 'koneksi.php';

function e($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
function ambil($k) { return (isset($_POST[$k]) && is_string($_POST[$k])) ? trim($_POST[$k]) : ''; }

$id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($id === false) {
    http_response_code(400);
    exit('ID materi tidak valid.');
}

$stmt = $conn->prepare('SELECT id, mata_kuliah_id, judul, isi, tautan FROM materi WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$lama = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$lama) {
    http_response_code(404);
    exit('Materi tidak ditemukan.');
}

$mata_kuliah_id = (string) $lama['mata_kuliah_id'];
$judul = $lama['judul'];
$isi = $lama['isi'];
$tautan = $lama['tautan'];
$errors = [];
$info = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mata_kuliah_id = ambil('mata_kuliah_id');
    $judul = ambil('judul');
    $isi = ambil('isi');
    $tautan = ambil('tautan');

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
        $errors[] = 'Judul materi harus 3-150 karakter.';
    }
    if (mb_strlen($isi) < 3 || mb_strlen($isi) > 2000) {
        $errors[] = 'Isi materi harus 3-2000 karakter.';
    }
    if (
        $tautan === ''
        || mb_strlen($tautan) > 255
        || !preg_match('#^https?://#i', $tautan)
        || !filter_var($tautan, FILTER_VALIDATE_URL)
    ) {
        $errors[] = 'Gunakan tautan http/https yang valid (maksimal 255 karakter).';
    }

    if (!$errors) {
        $berubah = $mk !== (int) $lama['mata_kuliah_id']
            || $judul !== $lama['judul']
            || $isi !== $lama['isi']
            || $tautan !== $lama['tautan'];

        if (!$berubah) {
            $info = 'Tidak ada perubahan data.';
        } else {
            try {
                $stmt = $conn->prepare(
                    'UPDATE materi SET mata_kuliah_id = ?, judul = ?, isi = ?, tautan = ? WHERE id = ?'
                );
                $stmt->bind_param('isssi', $mk, $judul, $isi, $tautan, $id);
                $stmt->execute();
                $stmt->close();

                header('Location: daftar_materi.php?pesan=edit');
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
    <title>Edit Materi</title>
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

    <h1>Edit Materi</h1>

    <?php if ($errors): ?>
        <div class="pesan error" role="alert">
            <ul><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>
    <?php if ($info !== ''): ?>
        <p class="pesan" role="status"><?= e($info) ?></p>
    <?php endif; ?>

    <form method="POST" action="edit_materi.php?id=<?= (int) $id ?>" class="form" novalidate>
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
            <label for="judul">Judul materi</label><br>
            <input type="text" id="judul" name="judul" maxlength="150" value="<?= e($judul) ?>">
        </p>
        <p>
            <label for="isi">Isi materi</label><br>
            <textarea id="isi" name="isi" rows="5"><?= e($isi) ?></textarea>
        </p>
        <p>
            <label for="tautan">Tautan referensi</label><br>
            <input type="text" id="tautan" name="tautan" maxlength="255" value="<?= e($tautan) ?>">
        </p>
        <div class="aksi">
            <button type="submit" class="btn btn-primary">Simpan data</button>
            <a class="btn" href="daftar_materi.php">Batal</a>
        </div>
    </form>
</div>
</body>
</html>
