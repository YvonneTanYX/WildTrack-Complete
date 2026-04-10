<?php
/**
 * api/health_worker.php
 *
 * Worker health record CRUD — uses existing `animal_health` table.
 * Table columns: health_id, animal_id, checkup_date, health_status,
 *                diagnosis, treatment, next_checkup, vet_in_charge
 *
 * GET    — list all health records
 * POST   — insert a new health record
 * PUT    — update a health record
 * DELETE — delete a health record
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../check_session.php';

header('Content-Type: application/json');
requireVisitorLogin();

$pdo    = getDB();
$userId = $_SESSION['user']['user_id'] ?? $_SESSION['user']['id'] ?? 0;
$method = $_SERVER['REQUEST_METHOD'];

// Resolve worker_id from user_id
function getWorkerId(PDO $pdo, int $userId): ?int {
    $stmt = $pdo->prepare("SELECT worker_id FROM workers WHERE user_id = ?");
    $stmt->execute([$userId]);
    $row = $stmt->fetch();
    return $row ? (int)$row['worker_id'] : null;
}

try {
    switch ($method) {

        // ── GET: all health records ────────────────────────────────────────
        case 'GET':
            $stmt = $pdo->prepare(
                "SELECT ah.*, a.name AS animal_name, w.full_name AS vet_name
                 FROM animal_health ah
                 LEFT JOIN animals a ON a.animal_id = ah.animal_id
                 LEFT JOIN workers w ON w.worker_id = ah.vet_in_charge
                 ORDER BY ah.checkup_date DESC, ah.health_id DESC
                 LIMIT 100"
            );
            $stmt->execute();
            $rows = $stmt->fetchAll();
            $records = array_map(fn($r) => [
                'id'             => (int)$r['health_id'],
                'animal'         => $r['animal_name']  ?? 'Unknown',
                'type'           => $r['health_status'] ?? 'Routine Check',
                'notes'          => $r['diagnosis']    ?? '',
                'treatment'      => $r['treatment']    ?? '',
                'next_checkup'   => $r['next_checkup'] ? date('d M Y', strtotime($r['next_checkup'])) : '',
                'rawNextCheckup' => $r['next_checkup'] ?? '',
                'dateStr'        => $r['checkup_date'] ? date('d M Y', strtotime($r['checkup_date'])) : 'Today',
                'vet'            => $r['vet_name']     ?? '',
            ], $rows);
            echo json_encode(['success' => true, 'records' => $records]);
            break;

        // ── POST: insert a new health record ──────────────────────────────
        case 'POST':
            $d        = json_decode(file_get_contents('php://input'), true) ?? [];
            $workerId = getWorkerId($pdo, $userId);
            $stmt     = $pdo->prepare(
                "INSERT INTO animal_health
                 (animal_id, checkup_date, health_status, diagnosis, treatment, next_checkup, vet_in_charge)
                 VALUES (?, CURDATE(), ?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                $d['animal_id']     ?? null,
                $d['health_status'] ?? 'Routine Check',
                $d['diagnosis']     ?? '',
                $d['treatment']     ?? '',
                !empty($d['next_checkup']) ? $d['next_checkup'] : null,
                $workerId,
            ]);
            echo json_encode(['success' => true, 'id' => (int)$pdo->lastInsertId()]);
            break;

        // ── PUT: update a health record ────────────────────────────────────
        case 'PUT':
            $d    = json_decode(file_get_contents('php://input'), true) ?? [];
            $stmt = $pdo->prepare(
                "UPDATE animal_health
                 SET health_status=?, diagnosis=?, treatment=?, next_checkup=?
                 WHERE health_id=?"
            );
            $stmt->execute([
                $d['health_status'] ?? 'Routine Check',
                $d['diagnosis']     ?? '',
                $d['treatment']     ?? '',
                !empty($d['next_checkup']) ? $d['next_checkup'] : null,
                (int)($d['id']      ?? 0),
            ]);
            echo json_encode(['success' => true]);
            break;

        // ── DELETE: remove a health record ─────────────────────────────────
        case 'DELETE':
            $d    = json_decode(file_get_contents('php://input'), true) ?? [];
            $stmt = $pdo->prepare("DELETE FROM animal_health WHERE health_id = ?");
            $stmt->execute([(int)($d['id'] ?? 0)]);
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
