<?php
/**
 * api/incident_worker.php
 *
 * Incident reports CRUD — uses `incidents` table.
 * Columns: id, animal_id (nullable), incident_type, severity (low/medium/high),
 *          description, reported_by (worker_id), reported_at
 *
 * GET    — list all incidents (joined with animal & worker name)
 * POST   — insert a new incident report
 * PUT    — update an incident report
 * DELETE — delete an incident report
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../check_session.php';

header('Content-Type: application/json');
requireVisitorLogin();

$pdo    = getDB();
$userId = $_SESSION['user']['user_id'] ?? $_SESSION['user']['id'] ?? 0;
$method = $_SERVER['REQUEST_METHOD'];

// Resolve worker_id from user_id (same helper as feeding_worker.php)
function getWorkerId(PDO $pdo, int $userId): ?int {
    $stmt = $pdo->prepare("SELECT worker_id FROM workers WHERE user_id = ?");
    $stmt->execute([$userId]);
    $row = $stmt->fetch();
    return $row ? (int)$row['worker_id'] : null;
}

try {
    switch ($method) {

        // ── GET: all incident reports ──────────────────────────────────────
        case 'GET':
            $stmt = $pdo->prepare(
                "SELECT i.*,
                        a.name AS animal_name,
                        w.full_name AS reported_by_name
                 FROM   incidents i
                 LEFT   JOIN animals a ON a.animal_id = i.animal_id
                 LEFT   JOIN workers w ON w.worker_id = i.reported_by
                 ORDER  BY i.reported_at DESC"
            );
            $stmt->execute();
            $rows    = $stmt->fetchAll();
            $records = array_map(fn($r) => [
                'id'               => (int)$r['id'],
                'animal_id'        => $r['animal_id'] ? (int)$r['animal_id'] : null,
                'animal_name'      => $r['animal_name']      ?? null,
                'incident_type'    => $r['incident_type']    ?? '',
                'severity'         => $r['severity']         ?? 'low',
                'description'      => $r['description']      ?? '',
                'reported_by_name' => $r['reported_by_name'] ?? '',
                'reported_at'      => $r['reported_at']      ?? '',
            ], $rows);
            echo json_encode(['success' => true, 'incidents' => $records]);
            break;

        // ── POST: insert a new incident report ────────────────────────────
        case 'POST':
            $d        = json_decode(file_get_contents('php://input'), true) ?? [];
            $workerId = getWorkerId($pdo, $userId);

            $animalId    = isset($d['animal_id']) && $d['animal_id'] ? (int)$d['animal_id'] : null;
            $incType     = trim($d['incident_type'] ?? '');
            $severity    = $d['severity']           ?? 'low';
            $description = trim($d['description']   ?? '');

            if (!$incType || !$description) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'incident_type and description are required.']);
                break;
            }
            if (!in_array($severity, ['low', 'medium', 'high'])) $severity = 'low';

            $stmt = $pdo->prepare(
                "INSERT INTO incidents (animal_id, incident_type, severity, description, reported_by)
                 VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->execute([$animalId, $incType, $severity, $description, $workerId]);
            echo json_encode(['success' => true, 'id' => (int)$pdo->lastInsertId()]);
            break;

        // ── PUT: update an incident report ────────────────────────────────
        case 'PUT':
            $d  = json_decode(file_get_contents('php://input'), true) ?? [];
            $id = (int)($d['id'] ?? 0);

            if (!$id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'id is required.']);
                break;
            }

            // Build SET clause only for fields that were sent
            $fields = [];
            $params = [];

            if (isset($d['description']))   { $fields[] = 'description = ?';   $params[] = trim($d['description']); }
            if (isset($d['incident_type'])) { $fields[] = 'incident_type = ?'; $params[] = trim($d['incident_type']); }
            if (isset($d['severity']) && in_array($d['severity'], ['low','medium','high'])) {
                $fields[] = 'severity = ?';
                $params[] = $d['severity'];
            }

            if (empty($fields)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Nothing to update.']);
                break;
            }

            $params[] = $id;
            $stmt     = $pdo->prepare("UPDATE incidents SET " . implode(', ', $fields) . " WHERE id = ?");
            $stmt->execute($params);
            echo json_encode(['success' => true]);
            break;

        // ── DELETE: remove an incident report ─────────────────────────────
        case 'DELETE':
            $d  = json_decode(file_get_contents('php://input'), true) ?? [];
            $id = (int)($d['id'] ?? 0);

            if (!$id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'id is required.']);
                break;
            }

            $stmt = $pdo->prepare("DELETE FROM incidents WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true]);
            break;

        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'DB error: ' . $e->getMessage()]);
}
