<?php
/**
 * api/MapData.php  — Zoo Map data API
 */

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require_once __DIR__ . '/../config/db.php';

try {
    $pdo = getDB();
    // REMOVED the problematic line: $pdo->exec("SET SESSION max_allowed_packet = 67108864");
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Create pins table if missing
$pdo->exec("CREATE TABLE IF NOT EXISTS `pins` (
    `id` VARCHAR(64) NOT NULL,
    `name` VARCHAR(100) NOT NULL DEFAULT '',
    `emoji` VARCHAR(10) NOT NULL DEFAULT '📍',
    `color` VARCHAR(20) NOT NULL DEFAULT '#2D5A27',
    `light` VARCHAR(20) NOT NULL DEFAULT '#EAF1E8',
    `zone` VARCHAR(50) NOT NULL DEFAULT '',
    `descr` TEXT,
    `animals` TEXT,
    `pos_x` FLOAT NOT NULL DEFAULT 50,
    `pos_y` FLOAT NOT NULL DEFAULT 50,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Ensure map_settings exists
$pdo->exec("CREATE TABLE IF NOT EXISTS `map_settings` (`map_id` INT NOT NULL DEFAULT 1, `map_image` LONGTEXT, PRIMARY KEY (`map_id`))");
$pdo->exec("INSERT IGNORE INTO `map_settings` (`map_id`, `map_image`) VALUES (1, '')");

$method = $_SERVER['REQUEST_METHOD'];

// GET animals by zone
if ($method === 'GET' && isset($_GET['animals_by_zone'])) {
    $zone = trim($_GET['animals_by_zone']);
    try {
        $stmt = $pdo->prepare("SELECT name, emoji FROM animals WHERE zone = ? ORDER BY name ASC");
        $stmt->execute([$zone]);
        $animals = $stmt->fetchAll(PDO::FETCH_ASSOC); // [{name, emoji}, ...]
        echo json_encode(['animals' => $animals]);
    } catch (PDOException $e) {
        echo json_encode(['animals' => [], 'error' => $e->getMessage()]);
    }
    exit;
}

// GET animal species
if ($method === 'GET' && isset($_GET['animal_species'])) {
    $animalName = trim($_GET['animal_species']);
    try {
        $stmt = $pdo->prepare("SELECT species FROM animals WHERE name = ?");
        $stmt->execute([$animalName]);
        $species = $stmt->fetchColumn();
        echo json_encode(['species' => $species ?: '']);
    } catch (PDOException $e) {
        echo json_encode(['species' => '', 'error' => $e->getMessage()]);
    }
    exit;
}

// GET zones (distinct from animals table)
if ($method === 'GET' && isset($_GET['zones'])) {
    try {
        $rows = $pdo->query("SELECT DISTINCT zone AS location_name FROM animals WHERE zone IS NOT NULL AND zone != '' ORDER BY zone ASC")->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['zones' => $rows]);
    } catch (PDOException $e) {
        echo json_encode(['zones' => [], 'error' => $e->getMessage()]);
    }
    exit;
}

// GET map data
if ($method === 'GET') {
    try {
        $pins = [];
        $result = $pdo->query("SELECT * FROM pins");
        if ($result) {
            foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $pins[] = [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'emoji' => $row['emoji'],
                    'color' => $row['color'],
                    'light' => $row['light'],
                    'zone' => $row['zone'] ?? '',
                    'desc' => $row['descr'] ?? '',
                    'animals' => $row['animals'] ? explode(',', $row['animals']) : [],
                    'pos' => ['x' => (float)$row['pos_x'], 'y' => (float)$row['pos_y']],
                ];
            }
        }
        $map = $pdo->query("SELECT map_image FROM map_settings WHERE map_id = 1")->fetch(PDO::FETCH_ASSOC);
        echo json_encode(['Map' => $map['map_image'] ?? '', 'Pins' => $pins]);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage(), 'Map' => '', 'Pins' => []]);
    }
    exit;
}

// POST save map
if ($method === 'POST') {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    
    if (!is_array($data)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid JSON']);
        exit;
    }
    
    try {
        // Save map image
        $pdo->prepare("INSERT INTO map_settings (map_id, map_image) VALUES (1, ?) ON DUPLICATE KEY UPDATE map_image = VALUES(map_image)")
            ->execute([$data['Map'] ?? '']);
        
        // Delete old pins
        $pdo->exec("DELETE FROM pins");
        
        // Insert new pins
        $stmt = $pdo->prepare("INSERT INTO pins (id, name, emoji, color, light, zone, descr, animals, pos_x, pos_y) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        foreach (($data['Pins'] ?? []) as $p) {
            $light = $p['light'] ?? ($p['color'] ?? '#2D5A27') . '22';
            $cleanDesc = strip_tags($p['desc'] ?? '');
            $animalsStr = is_array($p['animals'] ?? null) ? implode(',', $p['animals']) : ($p['animals'] ?? '');
            
            $stmt->execute([
                $p['id'] ?? uniqid('pin-'),
                $p['name'] ?? '',
                $p['emoji'] ?? '📍',
                $p['color'] ?? '#2D5A27',
                $light,
                $p['zone'] ?? '',
                $cleanDesc,
                $animalsStr,
                (float)($p['pos']['x'] ?? $p['pos_x'] ?? 50),
                (float)($p['pos']['y'] ?? $p['pos_y'] ?? 50),
            ]);
        }
        
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'error' => 'Method not allowed']);