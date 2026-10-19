<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$host     = '127.0.0.1';
$username = 'root';
$password = '@Jov17a#';          
$database = 'lms_paw';

try {
    $conn = new mysqli($host, $username, $password, $database);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $ex) {
    
    http_response_code(500);
    exit('Koneksi database gagal. Periksa MySQL, password, dan nama database.');
}
