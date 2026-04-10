<?php
/**
 * api/vaccinations_worker.php
 *
 * Vaccination records CRUD — uses `vaccinations` table.
 * Columns: id, animal_id, vaccine_name, date_given,
 *          next_due_date, vet_name, logged_by (worker_id), created_at
 *
 * GET    — list all vaccination records (joined with animal & worker name)
 * POST   — insert a new vaccination record
 * PUT    — update a vaccination record
 * DELETE — delete a vaccination record
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

        // ── GET: all vaccination records ──────────────────────────────────
        case 'GET':
            $stmt = $pdo->prepare(
                "SELECT v.*,
                        a.name    AS animal_name,
                        a.species AS animal_species,
                        w.full_name AS worker_name
                 FROM   vaccinations v
                 LEFT   JOIN animals a ON a.animal_id = v.animal_id
                 LEFT   JOIN workers w ON w.worker_id = v.logged_by
                 ORDER  BY v.date_given DESC, v.created_at DESC"
            );
            $stmt->execute();
            $rows    = $stmt->fetchAll();
            $records = array_map(fn($r) => [
                'id'             => (int)$r['id'],
                'animal_id'      => (int)$r['animal_id'],
                'animal_name'    => $r['animal_name']    ?? 'Unknown',
                'animal_species' => $r['animal_species'] ?? '',
                'vaccine_name'   => $r['vaccine_name']   ?? '',
                'date_given'     => $r['date_given']     ?? '',
                'next_due_date'  => $r['next_due_date']  ?? null,
                'vet_name'       => $r['vet_name']       ?? '',
                'worker_name'    => $r['worker_name']    ?? '',
                'created_at'     => $r['created_at']     ?? '',
            ], $rows);
            echo json_encode(['success' => true, 'vaccinations' => $records]);
            break;

        // ── POST: insert a new vaccination record ─────────────────────────
        case 'POST':
            $d        = json_decode(file_get_contents('php://input'), true) ?? [];
            $workerId = getWorkerId($pdo, $userId);

            $animalId    = isset($d['animal_id']) ? (int)$d['animal_id'] : null;
            $vaccineName = trim($d['vaccine_name']  ?? '');
            $dateGiven   = trim($d['date_given']    ?? '');
            $nextDue     = trim($d['next_due_date'] ?? '') ?: null;
            $vetName     = trim($d['vet_name']      ?? '') ?: null;

            if (!$animalId || !$vaccineName || !$dateGiven) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'animal_id, vaccine_name and date_given are required.']);
                break;
            }

            $stmt = $pdo->prepare(
                "INSERT INTO vaccinations
                 (animal_id, vaccine_name, date_given, next_due_date, vet_name, logged_by)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([$animalId, $vaccineName, $dateGiven, $nextDue, $vetName, $workerId]);
            echo json_encode(['success' => true, 'id' => (int)$pdo->lastInsertId()]);
            break;

        // ── PUT: update a vaccination record ──────────────────────────────
        case 'PUT':
            $d = json_decode(file_get_contents('php://input'), true) ?? [];

            $id          = (int)($d['id']           ?? 0);
            $vaccineName = trim($d['vaccine_name']  ?? '');
            $dateGiven   = trim($d['date_given']    ?? '');
            $nextDue     = trim($d['next_due_date'] ?? '') ?: null;
            $vetName     = trim($d['vet_name']      ?? '') ?: null;

            if (!$id || !$vaccineName || !$dateGiven) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'id, vaccine_name and date_given are required.']);
                break;
            }

            $stmt = $pdo->prepare(
                "UPDATE vaccinations
                 SET vaccine_name  = ?,
                     date_given    = ?,
                     next_due_date = ?,
                     vet_name      = ?
                 WHERE id = ?"
            );
            $stmt->execute([$vaccineName, $dateGiven, $nextDue, $vetName, $id]);
            echo json_encode(['success' => true]);
            break;

        // ── DELETE: remove a vaccination record ───────────────────────────
        case 'DELETE':
            $d  = json_decode(file_get_contents('php://input'), true) ?? [];
            $id = (int)($d['id'] ?? 0);

            if (!$id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'id is required.']);
                break;
            }

            $stmt = $pdo->prepare("DELETE FROM vaccinations WHERE id = ?");
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
