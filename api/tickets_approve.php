<?php
/**
 * api/tickets.php
 * Admin ticketing management API.
 *
 * GET  ?action=get_pending          — list all tickets grouped by booking_ref
 * GET  ?action=check_notifications  — unread admin notifications
 * POST ?action=approve_payment      — edit + approve a booking, generate QR codes, notify visitor
 * POST ?action=reject_payment       — reject a booking, notify visitor
 */

require_once __DIR__ . '/../config/helpers.php';
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$currentAdminId = $_SESSION['user']['user_id'] ?? null;

// ── Route ───────────────────────────────────────────────────────────────
switch ($action) {
    case 'get_pending':          getPending();          break;
    case 'check_notifications':  checkNotifications();  break;
    case 'approve_payment':      approvePayment();      break;
    case 'reject_payment':       rejectPayment();       break;
    default:
        respond(false, 'Unknown action.');
}

// ════════════════════════════════════════════════════════════════════════
// GET PENDING — returns all bookings (pending + recent approved/rejected)
// grouped by booking_ref so the admin sees one row per order
// ════════════════════════════════════════════════════════════════════════
function getPending(): void {
    requireRole('admin');
    $pdo = getDB();

    // One row per booking_ref; aggregate ticket types and count
    $stmt = $pdo->query("
        SELECT
            CASE
                WHEN t.status IN ('approved','rejected')
                THEN COALESCE(MAX(t.approved_by_name), 'Admin')
                ELSE NULL
            END AS approved_by_name,
            t.booking_ref,
            u.username AS admin_name,
            u.email,
            u.user_id,
            t.visit_date,
            t.purchase_date,
            t.payment_proof,
            t.status,
            GROUP_CONCAT(DISTINCT t.ticket_type ORDER BY t.ticket_type SEPARATOR ', ') AS ticket_types,
            COUNT(t.ticket_id)                                                          AS ticket_count,
            SUM(t.price)                                                                AS total_price,
            MIN(t.ticket_id)                                                            AS first_ticket_id,
            JSON_ARRAYAGG(t.ticket_id)                                                  AS ticket_ids
        FROM tickets t
        JOIN users u ON t.approved_by_id = u.user_id
        WHERE t.booking_ref IS NOT NULL
        GROUP BY t.booking_ref, u.username, u.email, u.user_id,
                 t.visit_date, t.purchase_date, t.payment_proof, t.status
        ORDER BY
            FIELD(t.status,'pending','approved','rejected'),
            t.purchase_date DESC
    ");

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Attach add-ons for each booking's first ticket
    foreach ($rows as &$row) {
        $aStmt = $pdo->prepare(
            "SELECT addon_type, quantity, price_per_pax, subtotal
             FROM ticket_addons WHERE ticket_id = ?"
        );
        $aStmt->execute([$row['first_ticket_id']]);
        $row['addons'] = $aStmt->fetchAll(PDO::FETCH_ASSOC);
        $row['ticket_ids'] = json_decode($row['ticket_ids'], true);
    }
    unset($row);

    $pending = array_filter($rows, fn($r) => $r['status'] === 'pending');

    respond(true, 'OK', [
        'payments'      => array_values($rows),
        'pending_count' => count($pending),
    ]);
}

// ════════════════════════════════════════════════════════════════════════
// CHECK NOTIFICATIONS — unread admin notifications
// ════════════════════════════════════════════════════════════════════════
function checkNotifications(): void {
    $user = requireRole('admin');
    $pdo  = getDB();

    $stmt = $pdo->prepare(
        "SELECT id, type, title, body, booking_ref, is_read, created_at
         FROM notifications
         WHERE user_id = ?
         ORDER BY created_at DESC
         LIMIT 30"
    );
    $stmt->execute([$user['user_id']]);

    respond(true, 'OK', ['notifications' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
}

// ════════════════════════════════════════════════════════════════════════
// APPROVE PAYMENT
// Body JSON fields:
//   booking_ref   (required)
//   ticket_type   (optional override — Adult | Child | Group)
//   visit_date    (optional override — YYYY-MM-DD)
//   price         (optional override — applied to ALL tickets in booking)
//   username      (optional override — updates users.username)
//   email         (optional override — updates users.email)
// ════════════════════════════════════════════════════════════════════════
function approvePayment(): void {
    $admin = requireRole('admin');
    $body  = jsonBody();

    $adminId   = $admin['user_id']  ?? null;
    $adminUser = $admin['username'] ?? 'Admin';

    // Resolve the best display name: prefer full_name from workers table
    $adminName = $adminUser;
    $pdo = getDB();
    if ($adminId) {
        $wRow = $pdo->prepare("SELECT full_name FROM workers WHERE user_id = ? LIMIT 1");
        $wRow->execute([$adminId]);
        $w = $wRow->fetch(PDO::FETCH_ASSOC);
        if ($w && !empty($w['full_name'])) {
            $adminName = $w['full_name'];
        }
    }

    $bookingRef = clean($body['booking_ref'] ?? '');
    if (!$bookingRef) respond(false, 'booking_ref is required.');

    // Fetch all tickets for this booking
    $stmt = $pdo->prepare(
        "SELECT t.ticket_id, t.user_id, t.ticket_type, t.price, t.visit_date
         FROM tickets t
         WHERE t.booking_ref = ? AND t.status = 'pending'"
    );
    $stmt->execute([$bookingRef]);
    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($tickets)) {
        respond(false, 'No pending tickets found for this booking.');
    }

    $userId = $tickets[0]['user_id'];

    // ── Apply optional edits ────────────────────────────────────────────
    $newType  = clean($body['ticket_type'] ?? '');
    $newDate  = clean($body['visit_date']  ?? '');
    $newPrice = isset($body['price']) ? floatval($body['price']) : null;
    $newName  = clean($body['username']    ?? '');
    $newEmail = clean($body['email']       ?? '');

    // Validate overrides
    $allowedTypes = ['Adult', 'Child', 'Group'];
    if ($newType && !in_array($newType, $allowedTypes)) {
        respond(false, 'Invalid ticket_type. Must be Adult, Child, or Group.');
    }
    if ($newDate && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $newDate)) {
        respond(false, 'Invalid visit_date format. Use YYYY-MM-DD.');
    }
    if ($newPrice !== null && $newPrice < 0) {
        respond(false, 'Price cannot be negative.');
    }

    try {
        $pdo->beginTransaction();

        // Update visitor details if provided
        if ($newName || $newEmail) {
            $setParts = [];
            $params   = [];
            if ($newName)  { $setParts[] = 'username = ?'; $params[] = $newName; }
            if ($newEmail) { $setParts[] = 'email = ?';    $params[] = $newEmail; }
            $params[] = $userId;
            $pdo->prepare("UPDATE users SET " . implode(', ', $setParts) . " WHERE user_id = ?")
                ->execute($params);
        }

        // Approve each ticket, generate QR, apply overrides
        $baseUrl    = 'http://localhost/WildTrack/verify.php?code=';
        $approvedIds = [];

        $updateStmt = $pdo->prepare(
            "UPDATE tickets
             SET status       = 'approved',
                 qr_code      = ?,
                 ticket_type  = COALESCE(NULLIF(?, ''), ticket_type),
                 visit_date   = COALESCE(NULLIF(?, ''), visit_date),
                 price        = COALESCE(?, price),
                 approved_by_name = ?
             WHERE ticket_id = ?"
        );

        foreach ($tickets as $t) {
            $qrCode = 'QR-' . strtoupper(dechex(crc32(uniqid()))) . substr(uniqid('', true), -6) . '-' . $userId;
            $qrUrl  = $baseUrl . $qrCode;

            $updateStmt->execute([
                $qrUrl,
                $newType  ?: null,
                $newDate  ?: null,
                $newPrice,
                $adminName,
                $t['ticket_id'],
            ]);
            $approvedIds[] = $t['ticket_id'];
        }

        // Increment voucher used_count if a voucher was linked
        $vStmt = $pdo->prepare(
            "SELECT v.id FROM vouchers v
             JOIN ticket_vouchers tv ON tv.voucher_id = v.id
             WHERE tv.booking_ref = ?
             LIMIT 1"
        );
        $vStmt->execute([$bookingRef]);
        $voucher = $vStmt->fetch();
        if ($voucher) {
            $pdo->prepare("UPDATE vouchers SET used_count = used_count + 1 WHERE id = ?")
                ->execute([$voucher['id']]);
        }

        // Mark the admin's new_payment_proof notification as read
        $pdo->prepare(
            "UPDATE notifications SET is_read = 1
             WHERE booking_ref = ? AND type = 'new_payment_proof'"
        )->execute([$bookingRef]);

        // Notify the visitor
        $pdo->prepare(
            "INSERT INTO notifications
                (user_id, type, title, body, ticket_ids, booking_ref)
             VALUES (?, 'booking_approved',
                     'Your booking has been approved!',
                     ?,
                     ?,
                     ?)"
        )->execute([
            $userId,
            'Booking ' . $bookingRef . ' has been approved. Your tickets and QR codes are ready. Visit date: ' . ($newDate ?: $tickets[0]['visit_date']) . '.',
            json_encode($approvedIds),
            $bookingRef,
        ]);

        $pdo->commit();

        respond(true, 'Booking approved and visitor notified.', [
            'booking_ref'  => $bookingRef,
            'approved_ids' => $approvedIds,
        ]);

    } catch (PDOException $e) {
        $pdo->rollBack();
        respond(false, 'Approval failed: ' . $e->getMessage());
    }
}

// ════════════════════════════════════════════════════════════════════════
// REJECT PAYMENT
// Body: { booking_ref, reason }
// ════════════════════════════════════════════════════════════════════════
function rejectPayment(): void {
    $admin = requireRole('admin');
    $body  = jsonBody();

    $bookingRef = clean($body['booking_ref'] ?? '');
    $reason     = clean($body['reason']      ?? 'Payment could not be verified.');
    if (!$bookingRef) respond(false, 'booking_ref is required.');

    $pdo = getDB();

    $stmt = $pdo->prepare(
        "SELECT ticket_id, user_id FROM tickets WHERE booking_ref = ? AND status = 'pending' LIMIT 1"
    );
    $stmt->execute([$bookingRef]);
    $ticket = $stmt->fetch();

    if (!$ticket) respond(false, 'No pending tickets found for this booking.');

    try {
        $pdo->beginTransaction();

        $pdo->prepare(
            "UPDATE tickets SET status = 'rejected' WHERE booking_ref = ? AND status = 'pending'"
        )->execute([$bookingRef]);

        // Mark admin notification read
        $pdo->prepare(
            "UPDATE notifications SET is_read = 1
             WHERE booking_ref = ? AND type = 'new_payment_proof'"
        )->execute([$bookingRef]);

        // Notify visitor
        $pdo->prepare(
            "INSERT INTO notifications
                (user_id, type, title, body, booking_ref)
             VALUES (?, 'booking_rejected',
                     'Booking not approved',
                     ?,
                     ?)"
        )->execute([
            $ticket['user_id'],
            'Booking ' . $bookingRef . ' was not approved. Reason: ' . $reason . '. Please contact support if you have questions.',
            $bookingRef,
        ]);

        $pdo->commit();

        respond(true, 'Booking rejected and visitor notified.');

    } catch (PDOException $e) {
        $pdo->rollBack();
        respond(false, 'Rejection failed: ' . $e->getMessage());
    }
}
