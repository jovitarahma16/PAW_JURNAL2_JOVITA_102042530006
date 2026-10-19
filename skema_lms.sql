CREATE DATABASE IF NOT EXISTS lms_paw
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE lms_paw;

DROP TABLE IF EXISTS tugas;
DROP TABLE IF EXISTS materi;
DROP TABLE IF EXISTS mata_kuliah;

CREATE TABLE mata_kuliah (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kode VARCHAR(20) NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL,
    dosen VARCHAR(100) NOT NULL,
    semester TINYINT UNSIGNED NOT NULL
);

CREATE TABLE materi (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    mata_kuliah_id INT UNSIGNED NOT NULL,
    judul VARCHAR(150) NOT NULL,
    isi TEXT NOT NULL,
    tautan VARCHAR(255) NOT NULL,
    FOREIGN KEY (mata_kuliah_id)
        REFERENCES mata_kuliah(id)
        ON DELETE RESTRICT
);

CREATE TABLE tugas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    mata_kuliah_id INT UNSIGNED NOT NULL,
    judul VARCHAR(150) NOT NULL,
    instruksi TEXT NOT NULL,
    tenggat DATE NOT NULL,
    status VARCHAR(20) NOT NULL,
    FOREIGN KEY (mata_kuliah_id)
        REFERENCES mata_kuliah(id)
        ON DELETE RESTRICT
);

INSERT INTO mata_kuliah (kode, nama, dosen, semester) VALUES
    ('PAW-101', 'Pemrograman Aplikasi Web', 'Tiara', 2),
    ('BD-101', 'Basis Data', 'Putri', 2);

INSERT INTO materi (mata_kuliah_id, judul, isi, tautan) VALUES
    (1, 'Pengenalan PHP', 'Materi pengenalan PHP dan sintaks dasar.', 'https://www.php.net/manual/en/intro-whatis.php'),
    (2, 'Pengenalan SQL', 'Materi dasar query SQL dan pengelolaan database.', 'https://dev.mysql.com/doc/');

INSERT INTO tugas (mata_kuliah_id, judul, instruksi, tenggat, status) VALUES
    (1, 'Latihan CRUD', 'Buat aplikasi CRUD sederhana menggunakan PHP.', '2026-11-01', 'Aktif'),
    (2, 'Latihan Query', 'Buat query SELECT, INSERT, UPDATE, dan DELETE.', '2026-11-05', 'Draf');
