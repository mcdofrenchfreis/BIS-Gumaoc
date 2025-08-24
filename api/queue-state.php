<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
session_start();

// Debug mode check
$debug_mode = isset($_GET['debug']) || isset($_POST['debug']);

if ($debug_mode) {
	echo json_encode([
		'debug' => true,
		'session_data' => $_SESSION,
		'session_id' => session_id(),
		'request_method' => $_SERVER['REQUEST_METHOD'],
		'headers' => getallheaders(),
		'timestamp' => date('c')
	]);
	exit;
}

// Check authentication - allow admin or kiosk sessions
$is_authenticated = false;
$auth_debug = [];

// Check for admin authentication
if (isset($_SESSION['admin_id']) && isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
	$is_authenticated = true;
	$auth_debug['method'] = 'admin_with_flag';
}
// Check for kiosk authentication
else if (isset($_SESSION['kiosk_logged_in']) && $_SESSION['kiosk_logged_in'] === true) {
	$is_authenticated = true;
	$auth_debug['method'] = 'kiosk';
}
// Legacy admin session check
else if (isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id'])) {
	$is_authenticated = true;
	$auth_debug['method'] = 'admin_legacy';
	// Set the flag for future requests
	$_SESSION['admin_logged_in'] = true;
}

$auth_debug['is_authenticated'] = $is_authenticated;
$auth_debug['admin_id'] = $_SESSION['admin_id'] ?? null;
$auth_debug['admin_logged_in'] = $_SESSION['admin_logged_in'] ?? null;
$auth_debug['kiosk_logged_in'] = $_SESSION['kiosk_logged_in'] ?? null;

if (!$is_authenticated) {
	http_response_code(401);
	echo json_encode([
		'success' => false, 
		'message' => 'Authentication required', 
		'auth_debug' => $auth_debug,
		'session_debug' => $_SESSION,
		'timestamp' => date('c')
	]);
	exit;
}

require_once __DIR__ . '/../includes/db_connect.php';

try {
	// Active windows with current ticket (if any)
	$windows_stmt = $pdo->query(
		"
		SELECT w.id, w.window_number, w.window_name, w.service_id, w.operator_name, w.is_active,
			w.current_ticket_id, w.last_called_at,
			t.ticket_number AS current_ticket_number,
			t.customer_name AS current_customer,
			t.status AS current_ticket_status
		FROM queue_windows w
		LEFT JOIN queue_tickets t ON w.current_ticket_id = t.id
		WHERE w.is_active = 1
		ORDER BY w.window_number
		"
	);
	$windows = $windows_stmt->fetchAll();

	// Tickets currently serving today
	$serving_stmt = $pdo->query(
		"
		SELECT t.id, t.ticket_number, t.customer_name, t.service_id, s.service_name,
			w.window_name, w.window_number, t.called_at, t.served_at
		FROM queue_tickets t
		JOIN queue_services s ON t.service_id = s.id
		LEFT JOIN queue_windows w ON w.current_ticket_id = t.id
		WHERE t.status = 'serving' AND (
			DATE(t.created_at) = CURDATE() OR 
			(SELECT COUNT(*) FROM queue_tickets WHERE DATE(created_at) = CURDATE()) = 0
		)
		ORDER BY t.called_at ASC
		"
	);
	$serving = $serving_stmt->fetchAll();
	foreach ($serving as &$sv) { $sv['is_ready_pickup'] = 0; }
	unset($sv);

	// Certificates marked as 'ready' today -> treat as ready for pick-up and merge in
	$ready_stmt = $pdo->query(
		"
		SELECT cr.id AS cert_id,
		       COALESCE(qt.ticket_number, CONCAT('CERT-', cr.id)) AS ticket_number,
		       cr.full_name AS customer_name,
		       COALESCE(s.service_name, cr.certificate_type) AS service_name,
		       w.window_name, w.window_number,
		       cr.submitted_at AS served_at
		FROM certificate_requests cr
		LEFT JOIN queue_tickets qt ON cr.queue_ticket_id = qt.id
		LEFT JOIN queue_services s ON qt.service_id = s.id
		LEFT JOIN queue_windows w ON w.current_ticket_id = qt.id
		WHERE cr.status = 'ready' AND DATE(cr.submitted_at) = CURDATE()
		ORDER BY cr.submitted_at ASC
		"
	);
	$ready = $ready_stmt->fetchAll();
	foreach ($ready as &$rd) { $rd['is_ready_pickup'] = 1; }
	unset($rd);
	$serving = array_merge($ready, $serving);

	// Combined waiting queue: both queue tickets and pending certificate requests
	$waiting_tickets = $pdo->query(
		"
		SELECT t.id, t.ticket_number, t.customer_name, t.service_id, s.service_name, s.service_code,
			t.priority_level, t.estimated_time, t.created_at,
			ROW_NUMBER() OVER (
				ORDER BY 
					CASE t.priority_level 
						WHEN 'urgent' THEN 1 
						WHEN 'priority' THEN 2 
						WHEN 'senior' THEN 2
						WHEN 'pwd' THEN 2
						WHEN 'pregnant' THEN 2
						ELSE 3 
					END,
					t.created_at ASC
			) as queue_position,
			'queue_ticket' as source_type
		FROM queue_tickets t
		JOIN queue_services s ON t.service_id = s.id
		LEFT JOIN certificate_requests cr ON cr.queue_ticket_id = t.id
		WHERE t.status = 'waiting' 
			AND (
				DATE(t.created_at) = CURDATE() OR 
				(SELECT COUNT(*) FROM queue_tickets WHERE DATE(created_at) = CURDATE()) = 0
			)
			AND (cr.id IS NULL OR cr.status = 'pending')
			
		UNION ALL
		
		SELECT 
			CONCAT('CERT-', cr.id) as id,
			COALESCE(cr.queue_ticket_number, CONCAT('BC-', DATE_FORMAT(cr.submitted_at, '%Y%m%d'), '-', LPAD(cr.id, 3, '0'))) as ticket_number,
			cr.full_name as customer_name,
			COALESCE(s.id, 1) as service_id,
			COALESCE(s.service_name, cr.certificate_type) as service_name,
			COALESCE(s.service_code, 'BC') as service_code,
			'normal' as priority_level,
			DATE_ADD(cr.submitted_at, INTERVAL 30 MINUTE) as estimated_time,
			cr.submitted_at as created_at,
			ROW_NUMBER() OVER (ORDER BY cr.submitted_at ASC) + 1000 as queue_position,
			'certificate_request' as source_type
		FROM certificate_requests cr
		LEFT JOIN queue_services s ON s.service_name = cr.certificate_type OR s.service_name LIKE CONCAT('%', SUBSTRING_INDEX(cr.certificate_type, ' ', 1), '%')
		WHERE cr.status = 'pending'
			AND cr.queue_ticket_id IS NULL
			AND (
				DATE(cr.submitted_at) = CURDATE() OR 
				(SELECT COUNT(*) FROM certificate_requests WHERE DATE(submitted_at) = CURDATE()) = 0
			)
			
		ORDER BY source_type, queue_position
		LIMIT 200
		"
	);
	$waiting_list = $waiting_tickets->fetchAll();

	try {
		// Combined stats - handle linked queue tickets and certificate requests properly
		// Count queue tickets first
		$qt_stats = $pdo->query("
			SELECT 
				COUNT(CASE WHEN status = 'waiting' THEN 1 END) AS qt_waiting,
				COUNT(CASE WHEN status = 'serving' THEN 1 END) AS qt_serving,
				COUNT(CASE WHEN status = 'completed' THEN 1 END) AS qt_completed,
				COUNT(CASE WHEN status = 'cancelled' THEN 1 END) AS qt_cancelled,
				COUNT(CASE WHEN status = 'no_show' THEN 1 END) AS qt_no_show
			FROM queue_tickets 
			WHERE DATE(created_at) = CURDATE()
		")->fetch();
		
		// Count certificate requests that are NOT linked to today's queue tickets
		$cr_stats = $pdo->query("
			SELECT 
				COUNT(CASE WHEN status = 'pending' THEN 1 END) AS cr_pending,
				COUNT(CASE WHEN status = 'processing' THEN 1 END) AS cr_processing,
				COUNT(CASE WHEN status = 'ready' THEN 1 END) AS cr_ready,
				COUNT(CASE WHEN status = 'released' THEN 1 END) AS cr_released
			FROM certificate_requests 
			WHERE DATE(submitted_at) = CURDATE()
				AND (queue_ticket_id IS NULL OR queue_ticket_id NOT IN (
					SELECT id FROM queue_tickets WHERE DATE(created_at) = CURDATE()
				))
		")->fetch();
		
		// Count certificate requests that ARE linked to today's queue tickets to determine their effective status
		$linked_stats = $pdo->query("
			SELECT 
				COUNT(CASE WHEN cr.status = 'pending' AND qt.status = 'waiting' THEN 1 END) AS linked_waiting,
				COUNT(CASE WHEN cr.status IN ('processing', 'ready') OR (cr.status IN ('pending', 'processing', 'ready') AND qt.status = 'serving') THEN 1 END) AS linked_serving,
				COUNT(CASE WHEN cr.status = 'released' OR qt.status = 'completed' THEN 1 END) AS linked_completed
			FROM certificate_requests cr
			INNER JOIN queue_tickets qt ON cr.queue_ticket_id = qt.id
			WHERE DATE(cr.submitted_at) = CURDATE() AND DATE(qt.created_at) = CURDATE()
		")->fetch();
		
		// For linked pairs, we need to subtract them from the raw queue ticket counts to avoid double counting
		// and then add back the effective count based on certificate status
		$linked_pairs_count = $pdo->query("
			SELECT COUNT(*) as count
			FROM certificate_requests cr
			INNER JOIN queue_tickets qt ON cr.queue_ticket_id = qt.id
			WHERE DATE(cr.submitted_at) = CURDATE() AND DATE(qt.created_at) = CURDATE()
		")->fetch()['count'] ?? 0;
		
		// Merge the results to avoid double counting
		$stats_result = [
			'qt_waiting' => (int)($qt_stats['qt_waiting'] ?? 0),
			'qt_serving' => (int)($qt_stats['qt_serving'] ?? 0),
			'qt_completed' => (int)($qt_stats['qt_completed'] ?? 0),
			'qt_cancelled' => (int)($qt_stats['qt_cancelled'] ?? 0),
			'qt_no_show' => (int)($qt_stats['qt_no_show'] ?? 0),
			'cr_pending' => (int)($cr_stats['cr_pending'] ?? 0),
			'cr_processing' => (int)($cr_stats['cr_processing'] ?? 0),
			'cr_ready' => (int)($cr_stats['cr_ready'] ?? 0),
			'cr_released' => (int)($cr_stats['cr_released'] ?? 0),
			'linked_waiting' => (int)($linked_stats['linked_waiting'] ?? 0),
			'linked_serving' => (int)($linked_stats['linked_serving'] ?? 0),
			'linked_completed' => (int)($linked_stats['linked_completed'] ?? 0),
			'current_date' => date('Y-m-d')
		];
		
		// Calculate combined stats (for linked pairs, certificate status takes precedence)
		$effective_qt_waiting = $stats_result['qt_waiting'] - $linked_pairs_count; // Remove linked tickets from raw count
		$effective_qt_serving = $stats_result['qt_serving']; // Keep raw serving count
		$effective_qt_completed = $stats_result['qt_completed']; // Keep raw completed count
		
		$waiting_count = $effective_qt_waiting + $stats_result['cr_pending'] + $stats_result['linked_waiting'];
		$serving_count = $effective_qt_serving + $stats_result['cr_processing'] + $stats_result['cr_ready'] + $stats_result['linked_serving'];
		$completed_count = $effective_qt_completed + $stats_result['cr_released'] + $stats_result['linked_completed'];
		$cancelled_count = $stats_result['qt_cancelled'];
		$no_show_count = $stats_result['qt_no_show'];
		$total_count = $waiting_count + $serving_count + $completed_count + $cancelled_count + $no_show_count;
		
		$stats = [
			'waiting' => $waiting_count,
			'serving' => $serving_count,
			'completed' => $completed_count,
			'cancelled' => $cancelled_count,
			'no_show' => $no_show_count,
			'total' => $total_count
		];
		
		$debug_info = [
			'current_date' => $stats_result['current_date'],
			'queue_tickets' => [
				'waiting' => $stats_result['qt_waiting'],
				'serving' => $stats_result['qt_serving'],
				'completed' => $stats_result['qt_completed'],
				'cancelled' => $stats_result['qt_cancelled'],
				'no_show' => $stats_result['qt_no_show']
			],
			'certificate_requests_unlinked' => [
				'pending' => $stats_result['cr_pending'],
				'processing' => $stats_result['cr_processing'],
				'ready' => $stats_result['cr_ready'],
				'released' => $stats_result['cr_released']
			],
			'certificate_requests_linked' => [
				'waiting' => $stats_result['linked_waiting'],
				'serving' => $stats_result['linked_serving'],
				'completed' => $stats_result['linked_completed']
			],
			'linked_pairs_count' => (int)$linked_pairs_count,
			'final_calculation' => [
				'waiting' => "effective_qt_waiting($effective_qt_waiting) + cr_pending({$stats_result['cr_pending']}) + linked_waiting({$stats_result['linked_waiting']}) = $waiting_count",
				'serving' => "qt_serving({$stats_result['qt_serving']}) + cr_processing({$stats_result['cr_processing']}) + cr_ready({$stats_result['cr_ready']}) + linked_serving({$stats_result['linked_serving']}) = $serving_count",
				'completed' => "qt_completed({$stats_result['qt_completed']}) + cr_released({$stats_result['cr_released']}) + linked_completed({$stats_result['linked_completed']}) = $completed_count",
				'note' => "Linked pairs ($linked_pairs_count) removed from raw qt_waiting to avoid double counting"
			]
		];

	echo json_encode([
		'success' => true,
		'windows' => $windows,
		'serving' => $serving,
		'waiting' => $waiting_list,
		'stats' => $stats,
		'timestamp' => date('c'),
		'debug' => $debug_info ?? []
	], JSON_NUMERIC_CHECK);
	
	} catch (Exception $statsException) {
		// If stats query fails, provide default values
		$stats = [
			'waiting' => 0,
			'serving' => 0,
			'completed' => 0,
			'cancelled' => 0,
			'no_show' => 0,
			'total' => 0
		];
		
		echo json_encode([
			'success' => true,
			'windows' => $windows,
			'serving' => $serving,
			'waiting' => $waiting_list,
			'stats' => $stats,
			'timestamp' => date('c'),
			'warning' => 'Stats unavailable: ' . $statsException->getMessage(),
			'debug' => $debug_info ?? []
		], JSON_NUMERIC_CHECK);
	}

} catch (Exception $e) {
	// Log the error for debugging
	error_log('Queue State API Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
	
	http_response_code(500);
	echo json_encode([
		'success' => false, 
		'message' => 'Error fetching queue state: ' . $e->getMessage(),
		'error_details' => [
			'file' => basename($e->getFile()),
			'line' => $e->getLine(),
			'trace' => $e->getTraceAsString()
		],
		'timestamp' => date('c')
	]);
}
?>


