<?php
// Configuración local. Las variables de entorno permiten probar otra base sin editar este archivo.
$host = getenv('UMBRAL_DB_HOST') ?: '127.0.0.1';
$dbname = getenv('UMBRAL_DB_NAME') ?: 'el_umbral_encantado';
$user = getenv('UMBRAL_DB_USER') ?: 'root';
$pass = getenv('UMBRAL_DB_PASS') !== false ? getenv('UMBRAL_DB_PASS') : '';
$port = getenv('UMBRAL_DB_PORT') ?: '3306';
try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
} catch (PDOException $e) {
    error_log('El Umbral Encantado: no se pudo conectar a MySQL.');
    http_response_code(503);
    exit('No se pudo conectar al restaurante. Comprueba MySQL y la importación de el_umbral_encantado.');
}
