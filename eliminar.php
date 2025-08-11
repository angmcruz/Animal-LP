<?php
// eliminar.php
$servername = "localhost";
$username   = "root";
$password   = "root";
$dbname     = "animaleslp";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  die("❌ Error de conexión: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['idAnimal'])) {
  die("Solicitud inválida: falta idAnimal.");
}

$id = (int) $_POST['idAnimal'];

// borramos foto fisica
$foto = null;
$stmt = $conn->prepare("SELECT foto FROM animales WHERE idAnimal = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($foto);
$existe = $stmt->fetch();
$stmt->close();

if (!$existe) {
  $conn->close();
  die("El registro no existe.");
}

// delete registro
$stmt = $conn->prepare("DELETE FROM animales WHERE idAnimal = ?");
$stmt->bind_param("i", $id);
$ok = $stmt->execute();
$stmt->close();


if ($ok && $foto) {
  $ruta = __DIR__ . "/imagenes/" . $foto;
  if (is_file($ruta)) { @unlink($ruta); }
}

$conn->close();

// cambiar a listar que no esta listo todavia
header("Location: index.php?msg=eliminado");
exit;
