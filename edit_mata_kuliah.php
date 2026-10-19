<?php
require 'koneksi.php';

function e($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
function ambil($k) { return (isset($_POST[$k]) && is_string($_POST[$k])) ? trim($_POST[$k]) : ''; }


$id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($id === false) {
    http_response_code(400);
    exit('ID mata kuliah tidak valid.');
}


$stmt = $conn->prepare('SELECT id, kode, nama, dosen, semester FROM mata_kuliah WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$lama = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$lama) {
    http_response_code(404);
    exit('Mata kuliah tidak ditemukan.');
}


$kode = $lama['kode'];
$nama = $lama['nama'];
$dosen = $lama['dosen'];
$semester = (string) $lama['semester'];
$errors = [];
$info = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode = strtoupper(ambil('kode'));
    $nama = ambil('nama');
    $dosen = ambil('dosen');
    $semester = ambil('semester');

    if (!preg_match('/^[A-Z0-9-]{3,20}$/', $kode)) {
        $errors[] = 'Kode harus 3-20 karakter: huruf kapital, angka, atau tanda -.';
    } else {
        
        $stmt = $conn->prepare('SELECT id FROM mata_kuliah WHERE kode = ? AND id <> ?');
        $stmt->bind_param('si', $kode, $id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = 'Kode sudah digunakan mata kuliah lain.';
        }
        $stmt->close();
    }

    if (mb_strlen($nama) < 3 || mb_strlen($nama) > 100) {
        $errors[] = 'Nama mata kuliah harus 3-100 karakter.';
    }
    if (mb_strlen($dosen) < 3 || mb_strlen($dosen) > 100) {
        $errors[] = 'Nama dosen harus 3-100 karakter.';
    }
    if (!preg_match('/^[0-9]+$/', $semester) || (int) $semester < 1 || (int) $semester > 14) {
        $errors[] = 'Semester harus bilangan bulat antara 1-14.';
    }

    if (!$errors) {
        $semester_int = (int) $semester;

       
        $berubah = $kode !== $lama['kode']
            || $nama !== $lama['nama']
            || $dosen !== $lama['dosen']
            || $semester_int !== (int) $lama['semester'];

        if (!$berubah) {
            $info = 'Tidak ada perubahan data.';
        } else {
            try {
                $stmt = $conn->prepare(
                    'UPDATE mata_kuliah SET kode = ?, nama = ?, dosen = ?, semester = ? WHERE id = ?'
                );
                $stmt->bind_param('sssii', $kode, $nama, $dosen, $semester_int, $id);
                $stmt->execute();
                $stmt->close();

                header('Location: daftar_mata_kuliah.php?pesan=edit');
                exit;
            } catch (mysqli_sql_exception $ex) {
                $errors[] = ($ex->getCode() === 1062)
                    ? 'Kode sudah digunakan mata kuliah lain.'
                    : 'Gagal memperbarui data.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Mata Kuliah</title>
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

    <h1>Edit Mata Kuliah</h1>

    <?php if ($errors): ?>
        <div class="pesan error" role="alert">
            <ul><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>
    <?php if ($info !== ''): ?>
        <p class="pesan" role="status"><?= e($info) ?></p>
    <?php endif; ?>

    
    <form method="POST" action="edit_mata_kuliah.php?id=<?= (int) $id ?>" class="form" novalidate>
        <p>
            <label for="kode">Kode mata kuliah</label><br>
            <input type="text" id="kode" name="kode" maxlength="20" value="<?= e($kode) ?>">
        </p>
        <p>
            <label for="nama">Nama mata kuliah</label><br>
            <input type="text" id="nama" name="nama" maxlength="100" value="<?= e($nama) ?>">
        </p>
        <p>
            <label for="dosen">Dosen pengampu</label><br>
            <input type="text" id="dosen" name="dosen" maxlength="100" value="<?= e($dosen) ?>">
        </p>
        <p>
            <label for="semester">Semester (1-14)</label><br>
            <input type="number" id="semester" name="semester" min="1" max="14" step="1" value="<?= e($semester) ?>">
        </p>
        <div class="aksi">
            <button type="submit" class="btn btn-primary">Simpan data</button>
            <a class="btn" href="daftar_mata_kuliah.php">Batal</a>
        </div>
    </form>
</div>
</body>
</html>
