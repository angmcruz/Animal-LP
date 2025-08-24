<?php
require_once __DIR__ . '/database.php';
$conn = db();
// Variables para búsqueda
$busqueda = "";
$animales = [];

// Si hay búsqueda, filtrar resultados
if (isset($_GET['buscar']) && !empty($_GET['buscar'])) {
    $busqueda = $_GET['buscar'];
    $sql = "SELECT * FROM animales WHERE nombre LIKE ? ORDER BY nombre ASC";
    $stmt = $conn->prepare($sql);
    $busquedaParam = "%" . $busqueda . "%";
    $stmt->bind_param("s", $busquedaParam);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
} else {
    // Mostrar todos los animales si no hay búsqueda
    $sql = "SELECT * FROM animales ORDER BY nombre ASC";
    $result = $conn->query($sql);
}

// Obtener resultados
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $animales[] = $row;
    }
}

$conn->close();

// Preparar datos para JavaScript (solo animales con coordenadas)
$animalesConCoordenadas = array_filter($animales, function($animal) {
    return !empty($animal['lat']) && !empty($animal['lng']);
});
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Animales - Ecuador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
          crossorigin="" />
    <style>
        .animal-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }
        .card-header {
            background: linear-gradient(135deg, #28a745, #20c997);
        }
        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
        }
        .badge-tipo {
            font-size: 0.8em;
        }
        #map {
            height: 400px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .popup-img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        .map-toggle {
            position: sticky;
            top: 10px;
            z-index: 1000;
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
                        <h4 class="mb-0">🦎 Lista de Animales de Ecuador</h4>
                    </div>
                    <div class="col-auto">
                        <a href="index.html" class="btn btn-light btn-sm">
                            🏠 Inicio
                        </a>
                        <a href="registro.html" class="btn btn-warning btn-sm">
                            ➕ Registrar Animal
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botón para mostrar/ocultar mapa -->
        <div class="map-toggle mb-3">
            <button id="toggleMap" class="btn btn-info">
                🗺️ Mostrar Mapa de Ubicaciones
            </button>
            <small class="text-muted ms-2">
                (<?php echo count($animalesConCoordenadas); ?> animales con ubicación GPS)
            </small>
        </div>

        <!-- Mapa (oculto inicialmente) -->
        <div class="card shadow-sm mb-4" id="mapContainer" style="display: none;">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">📍 Ubicaciones de los Animales</h5>
            </div>
            <div class="card-body">
                <div id="map"></div>
            </div>
        </div>

        <!-- Formulario de búsqueda -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-8">
                        <input type="text" 
                               class="form-control" 
                               name="buscar" 
                               placeholder="🔍 Buscar animal por nombre (ej: jaguar, colibrí...)"
                               value="<?php echo htmlspecialchars($busqueda); ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            Buscar
                        </button>
                    </div>
                    <div class="col-md-2">
                        <a href="listar.php" class="btn btn-secondary w-100">
                            Mostrar Todos
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Resultados -->
        <?php if (!empty($busqueda)): ?>
            <div class="alert alert-info">
                <strong>Resultados para:</strong> "<?php echo htmlspecialchars($busqueda); ?>" 
                (<?php echo count($animales); ?> encontrado<?php echo count($animales) != 1 ? 's' : ''; ?>)
            </div>
        <?php endif; ?>

        <?php if (empty($animales)): ?>
            <!-- Sin resultados -->
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <h5 class="text-muted">🔍 No se encontraron animales</h5>
                    <?php if (!empty($busqueda)): ?>
                        <p class="text-muted">Intenta con otro nombre o <a href="listar.php">ver todos los animales</a></p>
                    <?php else: ?>
                        <p class="text-muted">Aún no hay animales registrados</p>
                        <a href="registro.html" class="btn btn-success">Registrar el primer animal</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <!-- Tabla de resultados -->
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Foto</th>
                                    <th>Nombre</th>
                                    <th>Tipo</th>
                                    <th>Ecosistema</th>
                                    <th>Ubicación</th>
                                    <th>Coordenadas</th>
                                    <th>Descripción</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($animales as $animal): ?>
                                <tr data-id="<?php echo $animal['idAnimal']; ?>">
                                    <!-- Foto -->
                                    <td>
                                        <?php if (!empty($animal['foto']) && file_exists("imagenes/" . $animal['foto'])): ?>
                                            <img src="imagenes/<?php echo htmlspecialchars($animal['foto']); ?>" 
                                                 alt="<?php echo htmlspecialchars($animal['nombre']); ?>"
                                                 class="animal-img">
                                        <?php else: ?>
                                            <div class="animal-img bg-secondary d-flex align-items-center justify-content-center text-white">
                                                📷
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <!-- Nombre -->
                                    <td>
                                        <strong><?php echo htmlspecialchars($animal['nombre']); ?></strong>
                                    </td>
                                    
                                    <!-- Tipo -->
                                    <td>
                                        <?php 
                                        $coloresTipo = [
                                            'mamifero' => 'bg-primary',
                                            'ave' => 'bg-info',
                                            'reptil' => 'bg-success',
                                            'anfibio' => 'bg-warning',
                                            'pez' => 'bg-info',
                                            'insecto' => 'bg-secondary'
                                        ];
                                        $color = $coloresTipo[$animal['tipo']] ?? 'bg-dark';
                                        ?>
                                        <span class="badge <?php echo $color; ?> badge-tipo">
                                            <?php echo ucfirst(htmlspecialchars($animal['tipo'])); ?>
                                        </span>
                                    </td>
                                    
                                    <!-- Ecosistema -->
                                    <td>
                                        <small class="text-muted">
                                            <?php echo ucfirst(htmlspecialchars($animal['ecosistema'])); ?>
                                        </small>
                                    </td>
                                    
                                    <!-- Ubicación -->
                                    <td>
                                        <small><?php echo htmlspecialchars($animal['ubicacion']); ?></small>
                                    </td>

                                    <!-- Coordenadas -->
                                    <td>
                                        <?php if (!empty($animal['lat']) && !empty($animal['lng'])): ?>
                                            <small class="text-success">
                                                📍 <?php echo round($animal['lat'], 4); ?>, <?php echo round($animal['lng'], 4); ?>
                                                <br>
                                                <button class="btn btn-outline-primary btn-sm mt-1" 
                                                        onclick="mostrarEnMapa(<?php echo $animal['lat']; ?>, <?php echo $animal['lng']; ?>, '<?php echo htmlspecialchars($animal['nombre'], ENT_QUOTES); ?>')">
                                                    Ver en mapa
                                                </button>
                                            </small>
                                        <?php else: ?>
                                            <small class="text-muted">Sin coordenadas</small>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <!-- Descripción -->
                                    <td>
                                        <small>
                                            <?php 
                                            $desc = htmlspecialchars($animal['descripcion']);
                                            echo strlen($desc) > 40 ? substr($desc, 0, 40) . '...' : $desc;
                                            ?>
                                        </small>
                                    </td>
                                    
                                    <!-- Acciones -->
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="editar.php?id=<?php echo $animal['idAnimal']; ?>" 
                                               class="btn btn-warning btn-sm" 
                                               title="Editar <?php echo htmlspecialchars($animal['nombre']); ?>">
                                                ✏️
                                            </a>
                                            <form method="POST" 
                                                  action="eliminar.php" 
                                                  onsubmit="return confirm('¿Estás seguro de eliminar <?php echo htmlspecialchars($animal['nombre']); ?>?');"
                                                  class="d-inline">
                                                <input type="hidden" name="idAnimal" value="<?php echo $animal['idAnimal']; ?>">
                                                <button type="submit" class="btn btn-danger btn-sm" title="Eliminar">
                                                    🗑️
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Footer de la tabla -->
                <div class="card-footer text-muted text-center">
                    <small>
                        Total: <?php echo count($animales); ?> animal<?php echo count($animales) != 1 ? 'es' : ''; ?> 
                        | Con coordenadas: <?php echo count($animalesConCoordenadas); ?>
                        <?php if (!empty($busqueda)): ?>
                            | <a href="listar.php">Ver todos los animales</a>
                        <?php endif; ?>
                    </small>
                </div>
            </div>
        <?php endif; ?>

    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
            crossorigin=""></script>
    
    <script>
        let map;
        let markersGroup;
        let mapVisible = false;

        // Datos de los animales desde PHP
        const animales = <?php echo json_encode($animalesConCoordenadas, JSON_UNESCAPED_UNICODE); ?>;

        // Inicializar mapa
        function initMap() {
            // Centro de Ecuador
            map = L.map('map').setView([-1.8312, -78.1834], 6);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            markersGroup = L.layerGroup().addTo(map);
            
            // Agregar marcadores para cada animal
            animales.forEach(animal => {
                if (animal.lat && animal.lng) {
                    const marker = L.marker([parseFloat(animal.lat), parseFloat(animal.lng)]);
                    
                    // Crear popup personalizado
                    const popupContent = `
                        <div class="text-center">
                            ${animal.foto && animal.foto !== '' ? 
                                `<img src="imagenes/${animal.foto}" alt="${animal.nombre}" class="popup-img">` : 
                                `<div class="popup-img bg-secondary d-flex align-items-center justify-content-center text-white">
                                    <span style="font-size: 2rem;">📷</span>
                                </div>`
                            }
                            <h6><strong>${animal.nombre}</strong></h6>
                            <span class="badge bg-primary">${animal.tipo}</span><br>
                            <small class="text-muted">${animal.ecosistema} - ${animal.ubicacion}</small><br>
                            <p class="mt-2 mb-2">${animal.descripcion.length > 100 ? 
                                animal.descripcion.substring(0, 100) + '...' : 
                                animal.descripcion
                            }</p>
                            <a href="editar.php?id=${animal.idAnimal}" class="btn btn-warning btn-sm">
                                ✏️ Editar
                            </a>
                        </div>
                    `;
                    
                    marker.bindPopup(popupContent, {
                        maxWidth: 250,
                        className: 'custom-popup'
                    });
                    
                    markersGroup.addLayer(marker);
                }
            });

            // Ajustar vista si hay marcadores
            if (markersGroup.getLayers().length > 0) {
                map.fitBounds(markersGroup.getBounds(), {padding: [20, 20]});
            }
        }

        // Toggle del mapa
        document.getElementById('toggleMap').addEventListener('click', function() {
            const mapContainer = document.getElementById('mapContainer');
            const button = this;
            
            if (!mapVisible) {
                mapContainer.style.display = 'block';
                button.innerHTML = '🗺️ Ocultar Mapa';
                button.classList.remove('btn-info');
                button.classList.add('btn-secondary');
                
                if (!map) {
                    setTimeout(initMap, 100); // Pequeño delay para que el contenedor esté visible
                }
                mapVisible = true;
            } else {
                mapContainer.style.display = 'none';
                button.innerHTML = '🗺️ Mostrar Mapa de Ubicaciones';
                button.classList.remove('btn-secondary');
                button.classList.add('btn-info');
                mapVisible = false;
            }
        });

        // Función para mostrar animal específico en el mapa
        function mostrarEnMapa(lat, lng, nombre) {
            const mapContainer = document.getElementById('mapContainer');
            const toggleButton = document.getElementById('toggleMap');
            
            // Mostrar mapa si está oculto
            if (!mapVisible) {
                mapContainer.style.display = 'block';
                toggleButton.innerHTML = '🗺️ Ocultar Mapa';
                toggleButton.classList.remove('btn-info');
                toggleButton.classList.add('btn-secondary');
                mapVisible = true;
                
                if (!map) {
                    setTimeout(() => {
                        initMap();
                        map.setView([lat, lng], 12);
                        
                        // Encontrar y abrir el popup del animal específico
                        markersGroup.eachLayer(function(layer) {
                            if (layer.getLatLng().lat === lat && layer.getLatLng().lng === lng) {
                                layer.openPopup();
                            }
                        });
                    }, 100);
                    return;
                }
            }
            
            // Centrar en el animal y abrir popup
            map.setView([lat, lng], 12);
            
            markersGroup.eachLayer(function(layer) {
                if (Math.abs(layer.getLatLng().lat - lat) < 0.0001 && 
                    Math.abs(layer.getLatLng().lng - lng) < 0.0001) {
                    layer.openPopup();
                }
            });

            // Scroll suave al mapa
            mapContainer.scrollIntoView({ behavior: 'smooth' });
        }

        // Highlight del término buscado
        <?php if (!empty($busqueda)): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const termino = <?php echo json_encode($busqueda); ?>;
            const celdas = document.querySelectorAll('td strong');
            
            celdas.forEach(celda => {
                const texto = celda.textContent;
                if (texto.toLowerCase().includes(termino.toLowerCase())) {
                    const regex = new RegExp(`(${termino})`, 'gi');
                    celda.innerHTML = texto.replace(regex, '<mark>$1</mark>');
                }
            });
        });
        <?php endif; ?>
    </script>

</body>
</html>