<?php

require_once '../conexion.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

function jsonResponse($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

// OBTENER TANQUES
if ($method === 'GET' && $action === 'get_tanks') {
    try {
        $stmt = $pdo->query("SELECT id, nombre, tipo_combustible, capacidad, galones_actuales FROM tanques ORDER BY id DESC");
        $tanks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($tanks as &$tank) {
            $porcentaje = ($tank['capacidad'] > 0) ? ($tank['galones_actuales'] / $tank['capacidad']) * 100 : 0;
            $tank['porcentaje'] = round($porcentaje, 1);
            $tank['alerta'] = ($tank['galones_actuales'] <= ($tank['capacidad'] * 0.3));
        }
        jsonResponse(['success' => true, 'tanks' => $tanks]);
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
    }
}

// AGREGAR TANQUE
if ($method === 'POST' && $action === 'add_tank') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) jsonResponse(['success' => false, 'error' => 'Datos inválidos'], 400);

    $nombre = trim($input['nombre'] ?? '');
    $tipo = trim($input['tipo_combustible'] ?? '');
    $capacidad = (int)($input['capacidad'] ?? 0);
    $galonesIniciales = (int)($input['galones_iniciales'] ?? 0);

    if (empty($nombre) || empty($tipo) || $capacidad <= 0 || $galonesIniciales < 0) {
        jsonResponse(['success' => false, 'error' => 'Campos obligatorios o valores inválidos']);
    }
    if ($galonesIniciales > $capacidad) {
        jsonResponse(['success' => false, 'error' => 'Los galones iniciales no pueden superar la capacidad']);
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO tanques (nombre, tipo_combustible, capacidad, galones_actuales) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nombre, $tipo, $capacidad, $galonesIniciales]);
        jsonResponse(['success' => true, 'message' => 'Tanque agregado', 'tanque_id' => $pdo->lastInsertId()]);
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
    }
}

// ACTUALIZAR TANQUE (editar nombre, tipo, capacidad)
if ($method === 'POST' && $action === 'update_tank') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) jsonResponse(['success' => false, 'error' => 'Datos inválidos'], 400);

    $id = (int)($input['id'] ?? 0);
    $nombre = trim($input['nombre'] ?? '');
    $tipo = trim($input['tipo_combustible'] ?? '');
    $capacidad = (int)($input['capacidad'] ?? 0);

    if ($id <= 0 || empty($nombre) || empty($tipo) || $capacidad <= 0) {
        jsonResponse(['success' => false, 'error' => 'Datos inválidos']);
    }

    try {
        // Verificar que los galones actuales no superen la nueva capacidad
        $stmt = $pdo->prepare("SELECT galones_actuales FROM tanques WHERE id = ?");
        $stmt->execute([$id]);
        $tanque = $stmt->fetch();
        if (!$tanque) jsonResponse(['success' => false, 'error' => 'Tanque no encontrado']);
        if ($tanque['galones_actuales'] > $capacidad) {
            jsonResponse(['success' => false, 'error' => "Los galones actuales ({$tanque['galones_actuales']}) no pueden ser mayores a la nueva capacidad ($capacidad)"]);
        }

        $update = $pdo->prepare("UPDATE tanques SET nombre = ?, tipo_combustible = ?, capacidad = ? WHERE id = ?");
        $update->execute([$nombre, $tipo, $capacidad, $id]);
        jsonResponse(['success' => true, 'message' => 'Tanque actualizado']);
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
    }
}

// ELIMINAR TANQUE
if ($method === 'POST' && $action === 'delete_tank') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) jsonResponse(['success' => false, 'error' => 'Datos inválidos'], 400);

    $id = (int)($input['id'] ?? 0);
    if ($id <= 0) jsonResponse(['success' => false, 'error' => 'ID inválido']);

    try {
        $stmt = $pdo->prepare("DELETE FROM tanques WHERE id = ?");
        $stmt->execute([$id]);
        if ($stmt->rowCount() > 0) {
            jsonResponse(['success' => true, 'message' => 'Tanque eliminado']);
        } else {
            jsonResponse(['success' => false, 'error' => 'Tanque no encontrado']);
        }
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
    }
}

// REGISTRAR VENTA
if ($method === 'POST' && $action === 'add_sale') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) jsonResponse(['success' => false, 'error' => 'Datos inválidos'], 400);

    $tanque_id = (int)($input['tanque_id'] ?? 0);
    $galones = (int)($input['galones_vendidos'] ?? 0);

    if ($tanque_id <= 0 || $galones <= 0) {
        jsonResponse(['success' => false, 'error' => 'Datos de venta inválidos']);
    }

    try {
        $stmt = $pdo->prepare("SELECT galones_actuales, capacidad FROM tanques WHERE id = ?");
        $stmt->execute([$tanque_id]);
        $tanque = $stmt->fetch();
        if (!$tanque) jsonResponse(['success' => false, 'error' => 'Tanque no existe']);

        if ($galones > $tanque['galones_actuales']) {
            jsonResponse(['success' => false, 'error' => "Stock insuficiente. Disponible: {$tanque['galones_actuales']}"]);
        }

        $nuevos = $tanque['galones_actuales'] - $galones;
        $pdo->prepare("UPDATE tanques SET galones_actuales = ? WHERE id = ?")->execute([$nuevos, $tanque_id]);
        $pdo->prepare("INSERT INTO ventas (tanque_id, galones_vendidos) VALUES (?, ?)")->execute([$tanque_id, $galones]);

        jsonResponse(['success' => true, 'message' => 'Venta registrada', 'nuevos_galones' => $nuevos]);
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
    }
}

// REGISTRAR RECARGA
if ($method === 'POST' && $action === 'add_refill') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) jsonResponse(['success' => false, 'error' => 'Datos inválidos'], 400);

    $tanque_id = (int)($input['tanque_id'] ?? 0);
    $agregados = (int)($input['galones_agregados'] ?? 0);

    if ($tanque_id <= 0 || $agregados <= 0) {
        jsonResponse(['success' => false, 'error' => 'Datos de recarga inválidos']);
    }

    try {
        $stmt = $pdo->prepare("SELECT galones_actuales, capacidad FROM tanques WHERE id = ?");
        $stmt->execute([$tanque_id]);
        $tanque = $stmt->fetch();
        if (!$tanque) jsonResponse(['success' => false, 'error' => 'Tanque no existe']);

        $total = $tanque['galones_actuales'] + $agregados;
        if ($total > $tanque['capacidad']) {
            $max = $tanque['capacidad'] - $tanque['galones_actuales'];
            jsonResponse(['success' => false, 'error' => "No caben $agregados galones. Capacidad restante: $max"]);
        }

        $pdo->prepare("UPDATE tanques SET galones_actuales = ? WHERE id = ?")->execute([$total, $tanque_id]);
        $pdo->prepare("INSERT INTO recargas (tanque_id, galones_agregados) VALUES (?, ?)")->execute([$tanque_id, $agregados]);

        jsonResponse(['success' => true, 'message' => 'Recarga realizada', 'nuevos_galones' => $total]);
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
    }
}

// OBTENER VENTAS RECIENTES
if ($method === 'GET' && $action === 'recent_sales') {
    try {
        $stmt = $pdo->query("SELECT v.id, v.galones_vendidos, v.fecha, t.nombre as tanque_nombre FROM ventas v JOIN tanques t ON v.tanque_id = t.id ORDER BY v.fecha DESC LIMIT 10");
        jsonResponse(['success' => true, 'sales' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
    }
}

// OBTENER RECARGAS RECIENTES
if ($method === 'GET' && $action === 'recent_refills') {
    try {
        $stmt = $pdo->query("SELECT r.id, r.galones_agregados, r.fecha, t.nombre as tanque_nombre FROM recargas r JOIN tanques t ON r.tanque_id = t.id ORDER BY r.fecha DESC LIMIT 10");
        jsonResponse(['success' => true, 'refills' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
    }
}

jsonResponse(['success' => false, 'error' => 'Acción no válida'], 404);