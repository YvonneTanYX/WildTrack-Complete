<?php
/**
 * api/dailytask_worker.php
 *
 * Daily tasks CRUD — uses `daily_tasks` table.
 * Columns: id, name, meta, zone, priority (low/med/high),
 *          done (tinyint), active (tinyint),
 *          created_by (worker_id), created_at, updated_at
 *
 * GET    — list all tasks (optionally filtered by ?filter=active|done|inactive)
 * POST   — insert a new task
 * PUT    — update a task (supports partial update: done/active toggle)
 * DELETE — delete a task
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

        // ── GET: list tasks ────────────────────────────────────────────────
        case 'GET':
            $filter = $_GET['filter'] ?? 'all';

            $sql = "SELECT t.*, w.full_name AS created_by_name
                    FROM   daily_tasks t
                    LEFT   JOIN workers w ON w.worker_id = t.created_by";

            if ($filter === 'active')       $sql .= " WHERE t.active = 1 AND t.done = 0";
            elseif ($filter === 'done')     $sql .= " WHERE t.done = 1";
            elseif ($filter === 'inactive') $sql .= " WHERE t.active = 0";

            $sql .= " ORDER BY FIELD(t.priority,'high','med','low'), t.done ASC, t.created_at ASC";

            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetchAll();

            $tasks = array_map(fn($r) => [
                'id'              => (int)$r['id'],
                'name'            => $r['name']            ?? '',
                'meta'            => $r['meta']            ?? '',
                'zone'            => $r['zone']            ?? 'Zone A',
                'priority'        => $r['priority']        ?? 'med',
                'done'            => (bool)$r['done'],
                'active'          => (bool)$r['active'],
                'created_by_name' => $r['created_by_name'] ?? '',
                'created_at'      => $r['created_at']      ?? '',
            ], $rows);

            echo json_encode(['success' => true, 'tasks' => $tasks]);
            break;

        // ── POST: insert a new task ────────────────────────────────────────
        case 'POST':
            $d        = json_decode(file_get_contents('php://input'), true) ?? [];
            $workerId = getWorkerId($pdo, $userId);

            $name     = trim($d['name']     ?? '');
            $meta     = trim($d['meta']     ?? '') ?: 'Added manually';
            $zone     = trim($d['zone']     ?? 'Zone A');
            $priority = $d['priority']      ?? 'med';
            $active   = isset($d['active']) ? (int)(bool)$d['active'] : 1;

            if (!$name) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Task name is required.']);
                break;
            }
            if (!in_array($priority, ['low', 'med', 'high'])) $priority = 'med';

            $stmt = $pdo->prepare(
                "INSERT INTO daily_tasks (name, meta, zone, priority, done, active, created_by)
                 VALUES (?, ?, ?, ?, 0, ?, ?)"
            );
            $stmt->execute([$name, $meta, $zone, $priority, $active, $workerId]);
            echo json_encode(['success' => true, 'id' => (int)$pdo->lastInsertId()]);
            break;

        // ── PUT: update a task (full edit OR toggle done/active) ──────────
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

            if (isset($d['name']))     { $fields[] = 'name = ?';     $params[] = trim($d['name']); }
            if (isset($d['meta']))     { $fields[] = 'meta = ?';     $params[] = trim($d['meta']); }
            if (isset($d['zone']))     { $fields[] = 'zone = ?';     $params[] = trim($d['zone']); }
            if (isset($d['priority'])) { $fields[] = 'priority = ?'; $params[] = $d['priority']; }
            if (isset($d['done']))     { $fields[] = 'done = ?';     $params[] = (int)(bool)$d['done']; }
            if (isset($d['active'])) {
                $activeVal = (int)(bool)$d['active'];
                $fields[]  = 'active = ?';
                $params[]  = $activeVal;
                // Deactivating a task also un-completes it
                if (!$activeVal) { $fields[] = 'done = ?'; $params[] = 0; }
            }

            if (empty($fields)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Nothing to update.']);
                break;
            }

            $params[] = $id;
            $stmt     = $pdo->prepare("UPDATE daily_tasks SET " . implode(', ', $fields) . " WHERE id = ?");
            $stmt->execute($params);
            echo json_encode(['success' => true]);
            break;

        // ── DELETE: remove a task ─────────────────────────────────────────
        case 'DELETE':
            $d  = json_decode(file_get_contents('php://input'), true) ?? [];
            $id = (int)($d['id'] ?? 0);

            if (!$id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'id is required.']);
                break;
            }

            $stmt = $pdo->prepare("DELETE FROM daily_tasks WHERE id = ?");
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
