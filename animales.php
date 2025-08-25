<?php
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/database.php';
$conn = db();

$sql = "SELECT idAnimal, nombre, tipo, ecosistema, ubicacion, lat, lng, foto, descripcion 
        FROM animales
        ORDER BY nombre ASC";
$res = $conn->query($sql);

$animales= [];

if ($res) {
  while ($a = $res->fetch_assoc()) {
    $animales[] = [
        'idAnimal'   => (int)$a['idAnimal'],
        'nombre'     => $a['nombre'] ?? '',
        'tipo'       => $a['tipo'] ?? '',
        'ecosistema' => $a['ecosistema'] ?? '',
        'ubicacion'  => $a['ubicacion'] ?? '',
        'lat'        => (float)$a['lat'],
        'lng'        => (float)$a['lng'],
        'foto'       => $a['foto'] ?? '',
        'descripcion'=> $a['descripcion'] ?? '',
      ];
    };
};
$conn->close();
echo json_encode(['ok' => true, 'animales' => $animales], JSON_UNESCAPED_UNICODE);