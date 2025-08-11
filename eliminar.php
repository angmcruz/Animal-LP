<?php
// eliminar.php
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "AnimalesLP";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("❌ Error de conexión: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id'])) {
    die("Solicitud inválida");
}

$id = (int) $_POST['id'];

// Buscar la foto
$foto = null;
$stmt = $conn->prepare("SELECT foto FROM animales WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($foto);
$existe = $stmt->fetch();
$stmt->close();

if (!$existe) {
    $conn->close();
    die("El registro no existe.");
}

// Borrar registro
$stmt = $conn->prepare("DELETE FROM animales WHERE id = ?");
$stmt->bind_param("i", $id);
$ok = $stmt->execute();
$stmt->close();

// Borrar archivo físico
if ($ok && $foto) {
    $rutaAbs = __DIR__ . "/imagenes/" . $foto;
    if (is_file($rutaAbs)) {
        unlink($rutaAbs);
    }
}

$conn->close();

// Volver al listado
header("Location: listar.php?msg=eliminado");
exit;
?>
