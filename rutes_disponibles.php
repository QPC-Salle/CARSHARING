<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/classes/Sql.php';

$config = require __DIR__ . '/config.php';
$sql = new Sql($config);

// Obtenir filtres de la URL o POST
$filterOrigin = isset($_REQUEST['filter_origin']) ? trim($_REQUEST['filter_origin']) : '';
$filterDestination = isset($_REQUEST['filter_destination']) ? trim($_REQUEST['filter_destination']) : '';
$filterDate = isset($_REQUEST['filter_date']) ? trim($_REQUEST['filter_date']) : '';
$filterSeats = isset($_REQUEST['filter_seats']) ? (int)$_REQUEST['filter_seats'] : 0;

// Construir query dinàmicament
$query = "SELECT r.id, r.origin, r.destination, r.date_time, r.seats, r.description, u.nom as username 
         FROM rutes r 
         JOIN usuaris u ON r.user_email = u.correu 
         WHERE r.available = 1";
$params = [];

if (!empty($filterOrigin)) {
    $query .= " AND r.origin LIKE ?";
    $params[] = "%$filterOrigin%";
}
if (!empty($filterDestination)) {
    $query .= " AND r.destination LIKE ?";
    $params[] = "%$filterDestination%";
}
if (!empty($filterDate)) {
    $query .= " AND DATE(r.date_time) >= ?";
    $params[] = $filterDate;
}
if ($filterSeats > 0) {
    $query .= " AND r.seats >= ?";
    $params[] = $filterSeats;
}

$query .= " ORDER BY r.date_time ASC";

// Executar query
$rutes = empty($params) ? $sql->select($query) : $sql->select($query, $params);

// Si es una crida AJAX, retornar JSON
if (!empty($_REQUEST['ajax'])) {
    header('Content-Type: application/json');
    $result = [];
    foreach ($rutes as $r) {
        $result[] = [
            'id' => $r['id'],
            'origin' => htmlspecialchars($r['origin']),
            'destination' => htmlspecialchars($r['destination']),
            'date_time' => date('d/m/Y H:i', strtotime($r['date_time'])),
            'seats' => (int)$r['seats'],
            'username' => htmlspecialchars($r['username']),
            'description' => htmlspecialchars($r['description'])
        ];
    }
    echo json_encode($result);
    exit;
}
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rutes Disponibles - CarSharing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style_global.css">
    <link rel="stylesheet" href="css/style_filter.css">
</head>
<body class="bg-light">
    <?php require_once __DIR__ . '/includes/header.php'; ?>

    <main class="container py-5">
        <div class="mb-5">
            <h1 class="mb-1">Rutes Disponibles</h1>
            <p class="text-muted">Encuentra el viaje perfecto para ti</p>
        </div>

        <!-- FILTRES -->
        <div class="card mb-4 filter-card">
            <div class="card-body">
                <h6 class="mb-3"><i class="bi bi-funnel me-2"></i>Filtrar rutes</h6>
                <form id="filter-form" class="row g-3">
                    <div class="col-md-6 col-lg-3">
                        <label class="form-label">Origen</label>
                        <input type="text" class="form-control" name="filter_origin" placeholder="Ex: Barcelona" 
                               value="<?= htmlspecialchars($filterOrigin) ?>">
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <label class="form-label">Destí</label>
                        <input type="text" class="form-control" name="filter_destination" placeholder="Ex: Madrid"
                               value="<?= htmlspecialchars($filterDestination) ?>">
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <label class="form-label">Data mínima</label>
                        <input type="date" class="form-control" name="filter_date"
                               value="<?= htmlspecialchars($filterDate) ?>">
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <label class="form-label">Places mínimes</label>
                        <input type="number" class="form-control" name="filter_seats" min="0" max="8"
                               value="<?= $filterSeats > 0 ? $filterSeats : '' ?>" placeholder="Qualsevol">
                    </div>
                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-search me-2"></i>Filtrar</button>
                        <button type="reset" class="btn btn-outline-secondary"><i class="bi bi-arrow-clockwise me-2"></i>Netejar</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- RESULTATS -->
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8">
                <div id="results-container">
                    <?php if (empty($rutes)): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>No hi ha rutes que coincideixin amb els teus criteris.
                        </div>
                    <?php else: ?>
                        <?php foreach ($rutes as $rute): ?>
                            <div class="card mb-4 shadow-sm route-card">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <h5 class="card-title mb-3">
                                                <i class="bi bi-geo-alt text-primary me-2"></i>
                                                <?= htmlspecialchars($rute['origin']) ?> 
                                                <i class="bi bi-arrow-right mx-2"></i> 
                                                <?= htmlspecialchars($rute['destination']) ?>
                                            </h5>
                                            <div class="card-text text-muted small">
                                                <p class="mb-1">
                                                    <i class="bi bi-person-circle me-2"></i>
                                                    <?= htmlspecialchars($rute['username']) ?>
                                                </p>
                                                <p class="mb-1">
                                                    <i class="bi bi-calendar-event me-2"></i>
                                                    <?= date('d/m/Y H:i', strtotime($rute['date_time'])) ?>
                                                </p>
                                                <p class="mb-1">
                                                    <i class="bi bi-people me-2"></i>
                                                    <?= (int)$rute['seats'] ?> places disponibles
                                                </p>
                                                <?php if (!empty($rute['description'])): ?>
                                                    <p class="mb-0 mt-2">
                                                        <i class="bi bi-info-circle me-2"></i>
                                                        <?= htmlspecialchars(substr($rute['description'], 0, 100)) ?>
                                                        <?php if (strlen($rute['description']) > 100) echo '...'; ?>
                                                    </p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                            <a href="route_details.php?id=<?= (int)$rute['id'] ?>" class="btn btn-outline-primary">
                                                <i class="bi bi-eye me-2"></i>Veure detalls
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="text-center mt-5">
                    <a href="menu.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Tornar al menú
                    </a>
                </div>
            </div>
        </div>
    </main>

    <?php require_once __DIR__ . '/includes/footer.php'; ?>

    <script>
    // Filtrar en temps real amb AJAX (opcional)
    document.getElementById('filter-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('ajax', '1');
        
        try {
            const res = await fetch(window.location.pathname, {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            
            const container = document.getElementById('results-container');
            if (data.length === 0) {
                container.innerHTML = '<div class="alert alert-info"><i class="bi bi-info-circle me-2"></i>No hi ha rutes que coincideixin amb els teus criteris.</div>';
                return;
            }
            
            let html = '';
            data.forEach(rute => {
                html += `
                    <div class="card mb-4 shadow-sm route-card">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h5 class="card-title mb-3">
                                        <i class="bi bi-geo-alt text-primary me-2"></i>
                                        ${rute.origin} 
                                        <i class="bi bi-arrow-right mx-2"></i> 
                                        ${rute.destination}
                                    </h5>
                                    <div class="card-text text-muted small">
                                        <p class="mb-1"><i class="bi bi-person-circle me-2"></i>${rute.username}</p>
                                        <p class="mb-1"><i class="bi bi-calendar-event me-2"></i>${rute.date_time}</p>
                                        <p class="mb-1"><i class="bi bi-people me-2"></i>${rute.seats} places disponibles</p>
                                        ${rute.description ? `<p class="mb-0 mt-2"><i class="bi bi-info-circle me-2"></i>${rute.description.substring(0, 100)}${rute.description.length > 100 ? '...' : ''}</p>` : ''}
                                    </div>
                                </div>
                                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                    <a href="route_details.php?id=${rute.id}" class="btn btn-outline-primary">
                                        <i class="bi bi-eye me-2"></i>Veure detalls
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            container.innerHTML = html;
        } catch (err) {
            console.error('Error filtrando:', err);
        }
    });

    // Netejar form
    document.getElementById('filter-form').querySelector('button[type="reset"]').addEventListener('click', function() {
        setTimeout(() => document.getElementById('filter-form').submit(), 10);
    });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
