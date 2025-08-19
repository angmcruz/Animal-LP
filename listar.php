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
//solo para probar en postman
 //header('Content-Type: application/json');
//echo json_encode($animales, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Animales - Ecuador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
                                    <th>Descripción</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($animales as $animal): ?>
                                <tr>
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
                                    
                                    <!-- Descripción -->
                                    <td>
                                        <small>
                                            <?php 
                                            $desc = htmlspecialchars($animal['descripcion']);
                                            echo strlen($desc) > 50 ? substr($desc, 0, 50) . '...' : $desc;
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
    
    <!-- Script para confirmar eliminación -->
    <script>
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