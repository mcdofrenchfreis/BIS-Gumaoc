<?php
session_start();
$base_path = '../';
$page_title = 'Queue Kiosk Display - Barangay Gumaoc East';

include '../includes/db_connect.php';
include '../includes/QueueManager.php';

// Initialize queue manager
$queueManager = new QueueManager($pdo);

// Get real-time queue data
$queue_status = $queueManager->getQueueStatus();
$currently_serving = $queueManager->getCurrentlyServing();
$next_in_queue = $queueManager->getNextInQueue(10);

// Get today's overall statistics
$today_stats = [];
try {
    $stats_query = $pdo->query("
        SELECT 
            COUNT(*) as total_tickets,
            SUM(CASE WHEN status = 'waiting' THEN 1 ELSE 0 END) as waiting,
            SUM(CASE WHEN status = 'serving' THEN 1 ELSE 0 END) as serving,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
        FROM queue_tickets 
        WHERE DATE(created_at) = CURDATE()
    ");
    $today_stats = $stats_query->fetch();
} catch (Exception $e) {
    $today_stats = ['total_tickets' => 0, 'waiting' => 0, 'serving' => 0, 'completed' => 0];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1b5e20, #2e7d32, #388e3c);
            color: white;
            overflow-x: hidden;
            min-height: 100vh;
        }

        /* Kiosk Header */
        .kiosk-header {
            background: rgba(255, 255, 255, 0.1);
            padding: 30px;
            text-align: center;
            backdrop-filter: blur(10px);
            border-bottom: 2px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 40px;
        }

        .kiosk-title {
            font-size: 4rem;
            font-weight: 900;
            text-shadow: 3px 3px 6px rgba(0, 0, 0, 0.4);
            margin-bottom: 15px;
            letter-spacing: 3px;
        }

        .kiosk-subtitle {
            font-size: 1.8rem;
            opacity: 0.9;
            font-weight: 300;
        }

        .live-time {
            font-size: 1.4rem;
            margin-top: 15px;
            opacity: 0.8;
            font-family: 'Courier New', monospace;
        }

        /* Currently Serving Section */
        .serving-section {
            margin-bottom: 50px;
        }

        .section-title {
            font-size: 2.5rem;
            text-align: center;
            margin-bottom: 30px;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .serving-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 30px;
            padding: 0 30px;
        }

        .serving-card {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            backdrop-filter: blur(15px);
            border: 3px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            animation: pulse 2s infinite;
        }

        .serving-ticket-number {
            font-size: 6rem;
            font-weight: 900;
            font-family: 'Courier New', monospace;
            text-shadow: 4px 4px 8px rgba(0, 0, 0, 0.5);
            margin-bottom: 20px;
            color: #a5d6a7;
            text-stroke: 2px #000;
            -webkit-text-stroke: 2px #000;
        }

        .serving-service {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 15px;
            opacity: 0.9;
        }

        .serving-counter {
            font-size: 2.2rem;
            font-weight: 700;
            color: #4caf50;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .serving-customer {
            font-size: 2.2rem;
            margin-top: 20px;
            opacity: 0.9;
            font-style: italic;
            font-weight: 600;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.3);
        }

        /* Next in Queue Section */
        .next-section {
            margin-bottom: 50px;
        }

        .next-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            padding: 0 30px;
        }

        .next-card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .next-ticket-number {
            font-size: 3.5rem;
            font-weight: 900;
            font-family: 'Courier New', monospace;
            text-shadow: 3px 3px 6px rgba(0, 0, 0, 0.4);
            margin-bottom: 15px;
            color: #fff;
        }

        .next-service {
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: #c8e6c9;
        }

        .next-position {
            font-size: 1.8rem;
            font-weight: 700;
            color: #4caf50;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
        }

        /* Statistics Section */
        .stats-section {
            background: rgba(255, 255, 255, 0.1);
            margin: 40px 30px;
            border-radius: 20px;
            padding: 40px;
            backdrop-filter: blur(15px);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
        }

        .stat-card {
            text-align: center;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 30px;
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .stat-number {
            font-size: 4rem;
            font-weight: 900;
            text-shadow: 3px 3px 6px rgba(0, 0, 0, 0.4);
            margin-bottom: 15px;
        }

        .stat-label {
            font-size: 1.3rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.9;
        }

        .stat-waiting { color: #ff9800; }
        .stat-serving { color: #4caf50; }
        .stat-completed { color: #81c784; }
        .stat-total { color: #fff; }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px;
            opacity: 0.7;
        }

        .empty-state-icon {
            font-size: 8rem;
            margin-bottom: 30px;
        }

        .empty-state-text {
            font-size: 2rem;
            font-weight: 600;
        }

        /* Animations */
        @keyframes pulse {
            0% { transform: scale(1); box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2); }
            50% { transform: scale(1.02); box-shadow: 0 20px 45px rgba(0, 0, 0, 0.3); }
            100% { transform: scale(1); box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2); }
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .next-card, .stat-card {
            animation: slideInUp 0.6s ease-out;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .kiosk-title { font-size: 3rem; }
            .serving-ticket-number { font-size: 5rem; }
            .next-ticket-number { font-size: 3rem; }
        }

        @media (max-width: 768px) {
            .kiosk-title { font-size: 2.5rem; }
            .serving-ticket-number { font-size: 4rem; }
            .next-ticket-number { font-size: 2.5rem; }
            .serving-grid, .next-grid { grid-template-columns: 1fr; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
        }

        /* Auto-refresh indicator */
        .refresh-indicator {
            position: fixed;
            top: 20px;
            right: 20px;
            background: rgba(56, 142, 60, 0.9);
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            font-size: 1rem;
            font-weight: 600;
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 1000;
        }

        .refresh-indicator.show {
            opacity: 1;
        }
    </style>
</head>
<body>
    <!-- Auto-refresh indicator -->
    <div id="refreshIndicator" class="refresh-indicator">
        🔄 Updating...
    </div>

    <!-- Kiosk Header -->
    <div class="kiosk-header">
        <h1 class="kiosk-title">🎫 QUEUE MONITOR</h1>
        <p class="kiosk-subtitle">Barangay Gumaoc East - Live Queue Status</p>
        <div class="live-time" id="liveTime"></div>
    </div>

    <!-- Currently Serving Section -->
    <?php if (count($currently_serving) > 0): ?>
    <div class="serving-section">
        <h2 class="section-title">🎯 NOW SERVING</h2>
        <div class="serving-grid">
            <?php foreach ($currently_serving as $serving): ?>
            <div class="serving-card">
                <div class="serving-ticket-number"><?php echo htmlspecialchars($serving['ticket_number']); ?></div>
                <div class="serving-service"><?php echo htmlspecialchars($serving['service_name']); ?></div>
                <div class="serving-counter"><?php echo htmlspecialchars($serving['counter_name'] ?? 'Service Counter'); ?></div>
                <div class="serving-customer"><?php echo htmlspecialchars($serving['customer_name']); ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Next in Queue Section -->
    <?php if (count($next_in_queue) > 0): ?>
    <div class="next-section">
        <h2 class="section-title">⏭️ NEXT IN QUEUE</h2>
        <div class="next-grid">
            <?php foreach ($next_in_queue as $index => $next): ?>
            <div class="next-card" style="animation-delay: <?php echo $index * 0.1; ?>s;">
                <div class="next-ticket-number"><?php echo htmlspecialchars($next['ticket_number']); ?></div>
                <div class="next-service"><?php echo htmlspecialchars($next['service_name']); ?></div>
                <div class="next-position">Position #<?php echo $next['queue_position']; ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <div class="empty-state-icon">✅</div>
        <div class="empty-state-text">No tickets waiting in queue</div>
    </div>
    <?php endif; ?>

    <!-- Statistics Section -->
    <div class="stats-section">
        <h2 class="section-title">📊 TODAY'S STATISTICS</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number stat-waiting"><?php echo $today_stats['waiting']; ?></div>
                <div class="stat-label">Waiting</div>
            </div>
            <div class="stat-card">
                <div class="stat-number stat-serving"><?php echo $today_stats['serving']; ?></div>
                <div class="stat-label">Serving</div>
            </div>
            <div class="stat-card">
                <div class="stat-number stat-completed"><?php echo $today_stats['completed']; ?></div>
                <div class="stat-label">Completed</div>
            </div>
            <div class="stat-card">
                <div class="stat-number stat-total"><?php echo $today_stats['total_tickets']; ?></div>
                <div class="stat-label">Total Today</div>
            </div>
        </div>
    </div>

    <script>
        // Update live time
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleString('en-PH', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true
            });
            document.getElementById('liveTime').textContent = timeString;
        }

        // Auto-refresh page every 15 seconds
        function autoRefresh() {
            const indicator = document.getElementById('refreshIndicator');
            
            // Show refresh indicator
            indicator.classList.add('show');
            
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        }

        // Initialize
        updateTime();
        setInterval(updateTime, 1000);
        setInterval(autoRefresh, 15000); // Refresh every 15 seconds

        // Add keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            switch(e.key) {
                case 'F5':
                case 'r':
                case 'R':
                    e.preventDefault();
                    autoRefresh();
                    break;
                case 'Escape':
                    if (confirm('Exit kiosk mode?')) {
                        window.location.href = 'queue-status.php';
                    }
                    break;
                case 'f':
                case 'F':
                    // Toggle fullscreen
                    if (!document.fullscreenElement) {
                        document.documentElement.requestFullscreen();
                    } else {
                        document.exitFullscreen();
                    }
                    break;
            }
        });

        // Add touch/click anywhere to refresh (for touch screens)
        let touchTimer;
        document.addEventListener('touchstart', function() {
            touchTimer = setTimeout(() => {
                autoRefresh();
            }, 2000); // Hold for 2 seconds to refresh
        });

        document.addEventListener('touchend', function() {
            clearTimeout(touchTimer);
        });
    </script>
</body>
</html>