<?php
// unica conexion
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = 'root';
$DB_NAME = 'animaleslp';
$DB_CHARSET = 'utf8mb4';

function db(): mysqli {
  static $conn = null;
  if ($conn instanceof mysqli) return $conn;

  global $DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_CHARSET;
  $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
  if ($conn->connect_error) {
    die('Error de conexión: ' . $conn->connect_error);
  }
  if (! $conn->set_charset($DB_CHARSET)) {
    die('Error al establecer charset: ' . $conn->error);
  }
  return $conn;
}

function db_close(): void {
    $conn = db();
    $conn->close();
}