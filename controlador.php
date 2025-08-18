<?php
require_once __DIR__ . '/database.php';
$conn = db();

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    
    $nombre     = $_POST["nombre"];
    $tipo       = $_POST["tipo"];
    $ecosistema = $_POST["ecosistema"];
    $ubicacion  = $_POST["ubicacion"];
    $descripcion = $_POST["descripcion"];

  
    if (isset($_FILES["foto"]) && $_FILES["foto"]["error"] === 0) {

       
        $nombreArchivo = time() . "_" . basename($_FILES["foto"]["name"]);
        $rutaDestino = "imagenes/" . $nombreArchivo;

        
        $tipoArchivo = strtolower(pathinfo($rutaDestino, PATHINFO_EXTENSION));
        $formatosPermitidos = ["jpg", "jpeg", "png", "gif"];

        if (in_array($tipoArchivo, $formatosPermitidos)) {

            
            if (move_uploaded_file($_FILES["foto"]["tmp_name"], $rutaDestino)) {

                
                $sql = "INSERT INTO animales (nombre, tipo, ecosistema, ubicacion, foto, descripcion)
                        VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssss", $nombre, $tipo, $ecosistema, $ubicacion, $nombreArchivo, $descripcion);

                if ($stmt->execute()) {
                    echo "<div style='text-align:center; font-family:Arial; margin-top:20px;'>
                            <h3>✅ Animal registrado con éxito</h3>
                            <a href='registro.html'>Volver al formulario</a>
                          </div>";
                } else {
                    echo "Error al guardar: " . $conn->error;
                }

                $stmt->close();
            } else {
                echo "Error al subir la imagen.";
            }
        } else {
            echo "Formato de imagen no permitido. Solo JPG, JPEG, PNG o GIF.";
        }
    } else {
        echo " No se recibió ninguna imagen o hubo un error al subirla.";
    }
}

$conn->close();
?>
