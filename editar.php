<?php
require_once __DIR__ . '/database.php';
$conn = db();

$animal = null;
$mensaje = "";

// Verificar que se recibió un ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: listar.php");
    exit;
}

$id = (int) $_GET['id'];

// Procesar actualización
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre      = $_POST["nombre"];
    $tipo        = $_POST["tipo"];
    $ecosistema  = $_POST["ecosistema"];
    $ubicacion   = $_POST["ubicacion"];
    $descripcion = $_POST["descripcion"];

    
    $lat = isset($_POST["lat"]) && $_POST["lat"] !== '' ? (float)$_POST["lat"] : null;   
    $lng = isset($_POST["lng"]) && $_POST["lng"] !== '' ? (float)$_POST["lng"] : null;   
    
    $fotoActual = $_POST["foto_actual"];
    $nuevaFoto = $fotoActual; // Por defecto mantener la foto actual
    
    // Manejo de nueva foto
    if (isset($_FILES["foto"]) && $_FILES["foto"]["error"] === 0) {
        $nombreArchivo = time() . "_" . basename($_FILES["foto"]["name"]);
        $rutaDestino = "imagenes/" . $nombreArchivo;
        $tipoArchivo = strtolower(pathinfo($rutaDestino, PATHINFO_EXTENSION));
        $formatosPermitidos = ["jpg", "jpeg", "png", "gif"];
        
        if (in_array($tipoArchivo, $formatosPermitidos)) {
            if (move_uploaded_file($_FILES["foto"]["tmp_name"], $rutaDestino)) {
                // Eliminar foto anterior si existe
                if (!empty($fotoActual) && file_exists("imagenes/" . $fotoActual)) {
                    unlink("imagenes/" . $fotoActual);
                }
                $nuevaFoto = $nombreArchivo;
            }
        }
    }
    
    // Actualizar en base de datos (incluye lat y lng)
    $sql = "UPDATE animales 
            SET nombre=?, tipo=?, ecosistema=?, ubicacion=?, foto=?, descripcion=?, lat=?, lng=?
            WHERE idAnimal=?";
    $stmt = $conn->prepare($sql);
    // 6 strings + 2 doubles + 1 int
    $stmt->bind_param("ssssssddi", $nombre, $tipo, $ecosistema, $ubicacion, $nuevaFoto, $descripcion, $lat, $lng, $id);
    
    if ($stmt->execute()) {
        $mensaje = "<div class='alert alert-success'>✅ Animal actualizado con éxito</div>";
    } else {
        $mensaje = "<div class='alert alert-danger'>⚠ Error al actualizar: " . $conn->error . "</div>";
    }
    $stmt->close();
}

// Obtener datos del animal
$sql = "SELECT * FROM animales WHERE idAnimal = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $conn->close();
    header("Location: listar.php");
    exit;
}

$animal = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Animal - <?php echo htmlspecialchars($animal['nombre']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        .foto-preview {
            max-width: 200px;
            max-height: 200px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #ddd;
        }
        .card-header {
            background: linear-gradient(135deg, #ffc107, #fd7e14);
        }
        #mapEditar {
            height: 300px;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body class="bg-light">

    <div class="container mt-4">
        
        <!-- Header -->
        <div class="card shadow-sm mb-4">
            <div class="card-header text-white">
                <div class="row align-items-center">
                    <div class="col">
                        <h4 class="mb-0">✏️ Editar Animal: <?php echo htmlspecialchars($animal['nombre']); ?></h4>
                    </div>
                    <div class="col-auto">
                        <a href="listar.php" class="btn btn-light btn-sm">
                            ← Volver a la lista
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <?php echo $mensaje; ?>

        <div class="row">
            <!-- Formulario de edición -->
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">Datos del Animal</h5>
                    </div>
                    <div class="card-body">
                        <form action="editar.php?id=<?php echo $animal['idAnimal']; ?>" method="POST" enctype="multipart/form-data">
                            
                            <!-- Campo oculto para foto actual -->
                            <input type="hidden" name="foto_actual" value="<?php echo htmlspecialchars($animal['foto']); ?>">
                            
                            <!-- Nombre de la especie -->
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre de la especie</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="nombre" 
                                       name="nombre" 
                                       value="<?php echo htmlspecialchars($animal['nombre']); ?>" 
                                       required>
                            </div>

                            <!-- Tipo -->
                            <div class="mb-3">
                                <label for="tipo" class="form-label">Tipo</label>
                                <select class="form-select" id="tipo" name="tipo" required>
                                    <option value="">Seleccione...</option>
                                    <option value="mamifero" <?php echo ($animal['tipo'] == 'mamifero') ? 'selected' : ''; ?>>Mamífero</option>
                                    <option value="ave" <?php echo ($animal['tipo'] == 'ave') ? 'selected' : ''; ?>>Ave</option>
                                    <option value="reptil" <?php echo ($animal['tipo'] == 'reptil') ? 'selected' : ''; ?>>Reptil</option>
                                    <option value="anfibio" <?php echo ($animal['tipo'] == 'anfibio') ? 'selected' : ''; ?>>Anfibio</option>
                                    <option value="pez" <?php echo ($animal['tipo'] == 'pez') ? 'selected' : ''; ?>>Pez</option>
                                    <option value="insecto" <?php echo ($animal['tipo'] == 'insecto') ? 'selected' : ''; ?>>Insecto</option>
                                </select>
                            </div>

                            <!-- Ecosistema -->
                            <div class="mb-3">
                                <label for="ecosistema" class="form-label">Ecosistema</label>
                                <select class="form-select" id="ecosistema" name="ecosistema" required>
                                    <option value="">Seleccione...</option>
                                    <option value="bosque" <?php echo ($animal['ecosistema'] == 'bosque') ? 'selected' : ''; ?>>Bosque</option>
                                    <option value="selva" <?php echo ($animal['ecosistema'] == 'selva') ? 'selected' : ''; ?>>Selva</option>
                                    <option value="desierto" <?php echo ($animal['ecosistema'] == 'desierto') ? 'selected' : ''; ?>>Desierto</option>
                                    <option value="oceano" <?php echo ($animal['ecosistema'] == 'oceano') ? 'selected' : ''; ?>>Océano</option>
                                    <option value="rio" <?php echo ($animal['ecosistema'] == 'rio') ? 'selected' : ''; ?>>Río</option>
                                    <option value="pradera" <?php echo ($animal['ecosistema'] == 'pradera') ? 'selected' : ''; ?>>Pradera</option>
                                </select>
                            </div>

                            <!-- Ubicación (texto libre) -->
                            <div class="mb-3">
                                <label for="ubicacion" class="form-label">Ubicación</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="ubicacion" 
                                       name="ubicacion" 
                                       value="<?php echo htmlspecialchars($animal['ubicacion']); ?>" 
                                       placeholder="Ej. Amazonía, Ecuador" 
                                       required>
                            </div>

                            <!-- Coordenadas -->
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Latitud</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="lat" 
                                           name="lat" 
                                           value="<?php echo htmlspecialchars($animal['lat'] ?? ''); ?>" 
                                           placeholder="-0.1807">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Longitud</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="lng" 
                                           name="lng" 
                                           value="<?php echo htmlspecialchars($animal['lng'] ?? ''); ?>" 
                                           placeholder="-78.4678">
                                </div>
                            </div>

                            <!-- Mapa para seleccionar ubicación -->
                            <div class="mt-3 mb-3">
                                <label class="form-label">Seleccionar ubicación en el mapa</label>
                                <div id="mapEditar"></div>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-info btn-sm" id="btnGeoEditar">
                                        📍 Usar mi ubicación actual
                                    </button>
                                    <button type="button" class="btn btn-secondary btn-sm" id="btnCentrarEcuador">
                                        🇪🇨 Centrar en Ecuador
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-sm" id="btnLimpiarCoordenadas">
                                        🗑️ Limpiar coordenadas
                                    </button>
                                </div>
                            </div>

                            <!-- Descripción -->
                            <div class="mb-3">
                                <label for="descripcion" class="form-label">Descripción</label>
                                <textarea class="form-control" 
                                          id="descripcion" 
                                          name="descripcion" 
                                          rows="4" 
                                          required><?php echo htmlspecialchars($animal['descripcion']); ?></textarea>
                            </div>

                            <!-- Nueva Foto -->
                            <div class="mb-3">
                                <label for="foto" class="form-label">Nueva Foto (opcional)</label>
                                <input type="file" 
                                       class="form-control" 
                                       id="foto" 
                                       name="foto" 
                                       accept="image/*">
                                <div class="form-text">
                                    Deja vacío si no quieres cambiar la foto actual
                                </div>
                            </div>

                            <!-- Botones -->
                            <div class="text-end">
                                <a href="listar.php" class="btn btn-secondary me-2">Cancelar</a>
                                <button type="submit" class="btn btn-warning">
                                    ✏️ Actualizar Animal
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>

            <!-- Panel lateral con foto actual -->
            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">📸 Foto Actual</h6>
                    </div>
                    <div class="card-body text-center">
                        <?php if (!empty($animal['foto']) && file_exists("imagenes/" . $animal['foto'])): ?>
                            <img src="imagenes/<?php echo htmlspecialchars($animal['foto']); ?>" 
                                 alt="<?php echo htmlspecialchars($animal['nombre']); ?>"
                                 class="foto-preview mb-3">
                            <p class="text-muted small mb-0">
                                Archivo: <?php echo htmlspecialchars($animal['foto']); ?>
                            </p>
                        <?php else: ?>
                            <div class="foto-preview bg-secondary d-flex align-items-center justify-content-center text-white mx-auto mb-3"
                                 style="width: 200px; height: 200px;">
                                <span style="font-size: 3rem;">📷</span>
                            </div>
                            <p class="text-muted">Sin foto</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Información adicional -->
                <div class="card shadow-sm mt-3">
                    <div class="card-header bg-secondary text-white">
                        <h6 class="mb-0">ℹ️ Información</h6>
                    </div>
                    <div class="card-body">
                        <p><strong>ID:</strong> <?php echo $animal['idAnimal']; ?></p>
                        <p><strong>Tipo:</strong> 
                            <span class="badge bg-primary"><?php echo ucfirst(htmlspecialchars($animal['tipo'])); ?></span>
                        </p>
                        <p><strong>Ecosistema:</strong> <?php echo ucfirst(htmlspecialchars($animal['ecosistema'])); ?></p>
                        <p><strong>Ubicación:</strong> <?php echo htmlspecialchars($animal['ubicacion']); ?></p>
                        <!-- Mostrar coordenadas -->
                        <p class="mb-0"><strong>Lat/Lng:</strong>
                            <span id="coordenadasDisplay">
                                <?php
                                  $latTxt = isset($animal['lat']) ? $animal['lat'] : '—';
                                  $lngTxt = isset($animal['lng']) ? $animal['lng'] : '—';
                                  echo htmlspecialchars($latTxt) . ', ' . htmlspecialchars($lngTxt);
                                ?>
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <!-- Script para preview de nueva imagen -->
    <script>
        const fileInput = document.getElementById('foto');
        if (fileInput) {
            fileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const preview = document.querySelector('.foto-preview');
                        if (preview) {
                            preview.src = e.target.result;
                            preview.style.display = 'block';
                        }
                    }
                    reader.readAsDataURL(file);
                }
            });
        }
    </script>

    <!-- Script para el mapa interactivo -->
    <script>
        let mapEditar;
        let markerEditar;

        // Coordenadas actuales del animal (si existen)
        const coordenadasActuales = {
            lat: <?php echo !empty($animal['lat']) ? $animal['lat'] : 'null'; ?>,
            lng: <?php echo !empty($animal['lng']) ? $animal['lng'] : 'null'; ?>
        };

        // Inicializar mapa
        function initMapEditar() {
            // Centro inicial: coordenadas actuales del animal o centro de Ecuador
            let centerLat = coordenadasActuales.lat || -1.8312;
            let centerLng = coordenadasActuales.lng || -78.1834;
            let initialZoom = coordenadasActuales.lat ? 10 : 6;

            mapEditar = L.map('mapEditar').setView([centerLat, centerLng], initialZoom);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(mapEditar);

            // Si hay coordenadas actuales, mostrar marcador
            if (coordenadasActuales.lat && coordenadasActuales.lng) {
                markerEditar = L.marker([coordenadasActuales.lat, coordenadasActuales.lng])
                    .addTo(mapEditar)
                    .bindPopup('Ubicación actual del animal')
                    .openPopup();
            }

            // Evento click en el mapa
            mapEditar.on('click', function(e) {
                const lat = e.latlng.lat.toFixed(6);
                const lng = e.latlng.lng.toFixed(6);

                // Actualizar campos de entrada
                document.getElementById('lat').value = lat;
                document.getElementById('lng').value = lng;

                // Actualizar display de coordenadas
                updateCoordenadasDisplay(lat, lng);

                // Remover marcador anterior y crear uno nuevo
                if (markerEditar) {
                    mapEditar.removeLayer(markerEditar);
                }

                markerEditar = L.marker([lat, lng])
                    .addTo(mapEditar)
                    .bindPopup(`Lat: ${lat}<br>Lng: ${lng}`)
                    .openPopup();
            });
        }

        // Actualizar display de coordenadas en el panel lateral
        function updateCoordenadasDisplay(lat, lng) {
            document.getElementById('coordenadasDisplay').textContent = `${lat}, ${lng}`;
        }

        // Usar geolocalización
        document.getElementById('btnGeoEditar').addEventListener('click', function() {
            if ("geolocation" in navigator) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    const lat = position.coords.latitude.toFixed(6);
                    const lng = position.coords.longitude.toFixed(6);

                    // Actualizar mapa
                    mapEditar.setView([lat, lng], 15);

                    // Actualizar campos
                    document.getElementById('lat').value = lat;
                    document.getElementById('lng').value = lng;

                    // Actualizar display
                    updateCoordenadasDisplay(lat, lng);

                    // Actualizar marcador
                    if (markerEditar) {
                        mapEditar.removeLayer(markerEditar);
                    }

                    markerEditar = L.marker([lat, lng])
                        .addTo(mapEditar)
                        .bindPopup('Tu ubicación actual')
                        .openPopup();

                }, function(error) {
                    alert('Error obteniendo ubicación: ' + error.message);
                });
            } else {
                alert('Geolocalización no soportada por este navegador');
            }
        });

        // Centrar en Ecuador
        document.getElementById('btnCentrarEcuador').addEventListener('click', function() {
            mapEditar.setView([-1.8312, -78.1834], 6);
        });

        // Limpiar coordenadas
        document.getElementById('btnLimpiarCoordenadas').addEventListener('click', function() {
            document.getElementById('lat').value = '';
            document.getElementById('lng').value = '';
            updateCoordenadasDisplay('—', '—');
            
            if (markerEditar) {
                mapEditar.removeLayer(markerEditar);
                markerEditar = null;
            }
        });

        // Sincronizar campos de texto con el mapa
        function syncMapWithInputs() {
            const lat = parseFloat(document.getElementById('lat').value);
            const lng = parseFloat(document.getElementById('lng').value);

            if (!isNaN(lat) && !isNaN(lng)) {
                mapEditar.setView([lat, lng], 10);
                
                if (markerEditar) {
                    mapEditar.removeLayer(markerEditar);
                }

                markerEditar = L.marker([lat, lng])
                    .addTo(mapEditar)
                    .bindPopup(`Lat: ${lat}<br>Lng: ${lng}`);

                updateCoordenadasDisplay(lat.toFixed(6), lng.toFixed(6));
            }
        }

        // Eventos para sincronizar cuando se escriba manualmente
        document.getElementById('lat').addEventListener('blur', syncMapWithInputs);
        document.getElementById('lng').addEventListener('blur', syncMapWithInputs);

        // Inicializar mapa cuando la página cargue
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(initMapEditar, 100);
        });
    </script>

</body>
</html>