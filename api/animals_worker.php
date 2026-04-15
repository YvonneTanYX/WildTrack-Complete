<?php
/**
 * api/animals_worker.php
 *
 * Serves animals for the worker dashboard.
 * Your `animals` table has: animal_id, name, species, diet, zone,
 * date_added, emoji, age, weight, gender, notes, status.
 *
 * NOTE: habitat and conservation_status do NOT exist in the DB — removed.
 *
 * GET    — list all animals
 * POST   — add a new animal
 * PUT    — update an animal
 * DELETE — delete an animal
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../check_session.php';

header('Content-Type: application/json');
requireVisitorLogin();

$pdo    = getDB();
$method = $_SERVER['REQUEST_METHOD'];

// ── Add extended worker columns if they don't exist yet ──────────────────────
// Safe to run every request — each ALTER is a no-op if the column already exists.
$extensions = [
    "ALTER TABLE animals ADD COLUMN IF NOT EXISTS emoji       VARCHAR(10)  DEFAULT '🐾'",
    "ALTER TABLE animals ADD COLUMN IF NOT EXISTS age         VARCHAR(30)  DEFAULT 'Unknown'",
    "ALTER TABLE animals ADD COLUMN IF NOT EXISTS weight      VARCHAR(30)  DEFAULT 'Unknown'",
    "ALTER TABLE animals ADD COLUMN IF NOT EXISTS gender      VARCHAR(20)  DEFAULT 'Unknown'",
    "ALTER TABLE animals ADD COLUMN IF NOT EXISTS zone        VARCHAR(50)  DEFAULT NULL",
    "ALTER TABLE animals ADD COLUMN IF NOT EXISTS notes       TEXT         DEFAULT NULL",
    "ALTER TABLE animals ADD COLUMN IF NOT EXISTS status      VARCHAR(20)  DEFAULT 'healthy'",
];
foreach ($extensions as $sql) {
    try { $pdo->exec($sql); } catch (PDOException $e) { /* column exists */ }
}

// Helper: map a DB row → JS-friendly object
function rowToAnimal(array $r): array {
    return [
        'id'      => (int)$r['animal_id'],
        'name'    => $r['name'],
        'species' => $r['species']  ?? '',
        'diet'    => $r['diet']     ?? '',
        'emoji'   => $r['emoji']    ?? '🐾',
        'age'     => $r['age']      ?? 'Unknown',
        'weight'  => $r['weight']   ?? 'Unknown',
        'gender'  => $r['gender']   ?? 'Unknown',
        'zone'    => $r['zone']     ?? '',
        'notes'   => $r['notes']    ?? '',
        'status'  => $r['status']   ?? 'healthy',
    ];
}

try {
    switch ($method) {

        // ── GET: list all animals ─────────────────────────────────────────
        case 'GET':
            $rows = $pdo->query(
                "SELECT * FROM animals ORDER BY name ASC"
            )->fetchAll();
            echo json_encode([
                'success' => true,
                'animals' => array_map('rowToAnimal', $rows),
            ]);
            break;

        // ── POST: add a new animal ────────────────────────────────────────
        case 'POST':
            $d = json_decode(file_get_contents('php://input'), true) ?? [];
            if (empty($d['name']) || empty($d['species'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'name and species required']);
                exit;
            }
            $stmt = $pdo->prepare(
                "INSERT INTO animals
                 (name, species, diet,
                  emoji, age, weight, gender, zone, notes, status, date_added)
                 VALUES (?,?,?,?,?,?,?,?,?,?,CURDATE())"
            );
            $stmt->execute([
                $d['name'], $d['species'],
                $d['diet'] ?? '',
                $d['emoji'] ?? '🐾', $d['age'] ?? 'Unknown',
                $d['weight'] ?? 'Unknown', $d['gender'] ?? 'Unknown',
                $d['zone'] ?? '', $d['notes'] ?? '',
                $d['status'] ?? 'healthy',
            ]);
            echo json_encode(['success' => true, 'id' => (int)$pdo->lastInsertId()]);
            break;

        // ── PUT: update an animal ─────────────────────────────────────────
        case 'PUT':
            $d = json_decode(file_get_contents('php://input'), true) ?? [];
            if (empty($d['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'id required']);
                exit;
            }
            $stmt = $pdo->prepare(
                "UPDATE animals SET
                 name=?, species=?, diet=?,
                 emoji=?, age=?, weight=?, gender=?, zone=?, notes=?, status=?
                 WHERE animal_id=?"
            );
            $stmt->execute([
                $d['name'] ?? '', $d['species'] ?? '',
                $d['diet'] ?? '',
                $d['emoji'] ?? '🐾', $d['age'] ?? 'Unknown',
                $d['weight'] ?? 'Unknown', $d['gender'] ?? 'Unknown',
                $d['zone'] ?? '', $d['notes'] ?? '',
                $d['status'] ?? 'healthy',
                (int)$d['id'],
            ]);
            echo json_encode(['success' => true]);
            break;

        // ── DELETE: remove an animal ──────────────────────────────────────
        case 'DELETE':
            $d = json_decode(file_get_contents('php://input'), true) ?? [];
            if (empty($d['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'id required']);
                exit;
            }
            $stmt = $pdo->prepare("DELETE FROM animals WHERE animal_id = ?");
            $stmt->execute([(int)$d['id']]);
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
