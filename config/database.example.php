<?php
declare(strict_types=1);

/**
 * Veritabanı ayarları.
 * Lokal ortam için varsayılan değerler tanımlanmıştır.
 */
const DB_HOST = '127.0.0.1';
const DB_PORT = 3307;
const DB_NAME = 'eczane_stok';
const DB_USER = 'root';
const DB_PASS = 'BURAYA_SIFRE';
const DB_CHARSET = 'utf8mb4';

/**
 * Tek bir PDO bağlantısı döndürür.
 */
function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    return $pdo;
}
