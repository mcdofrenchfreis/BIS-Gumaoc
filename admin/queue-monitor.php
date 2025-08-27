<?php
session_start();
include '../includes/db_connect.php';

// Check admin authentication
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$page_title = 'Queue Live Monitor - Admin Dashboard';
include '../includes/admin_header.php';
?>
<style>
/* Queue Monitor - Enhanced Admin Theme Compatible */
:root {
    --primary-green: #2e7d32;
    --primary-dark: #1b5e20;
    --primary-light: #388e3c;
    --success-green: #4caf50;
    --warning-orange: #ff9800;
    --danger-red: #f44336;
    --info-blue: #2196f3;
    --bg-light: #f4f7f9;
    --bg-white: #ffffff;
    --text-primary: #2c3e50;
    --text-secondary: #546e7a;
    --text-muted: #90a4ae;
    --border-light: #e8eaf6;
    --shadow-sm: 0 2px 4px rgba(0,0,0,0.05);
    --shadow-md: 0 4px 12px rgba(0,0,0,0.1);
    --shadow-lg: 0 8px 25px rgba(0,0,0,0.15);
    --border-radius: 12px;
    --border-radius-sm: 8px;
    --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Override admin header text layout issues */
.admin-brand-text {
    display: flex !important;
    flex-direction: column !important;
    align-items: flex-start !important;
    justify-content: center !important;
    line-height: 1.2 !important;
    min-height: 44px !important;
}

.admin-brand-text h1 {
    font-size: 18px !important;
    font-weight: 700 !important;
    margin: 0 0 2px 0 !important;
    color: white !important;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2) !important;
    line-height: 1.1 !important;
    white-space: nowrap !important;
}

.admin-brand-text p {
    font-size: 11px !important;
    color: rgba(255, 255, 255, 0.8) !important;
    margin: 0 !important;
    line-height: 1.1 !important;
    white-space: nowrap !important;
}

.admin-brand {
    display: flex !important;
    align-items: center !important;
    text-decoration: none !important;
    color: white !important;
    transition: all 0.3s ease !important;
    padding: 8px 12px !important;
    border-radius: 8px !important;
    min-width: 200px !important;
    height: 54px !important;
}

.admin-brand-logo {
    width: 36px !important;
    height: 36px !important;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.25), rgba(255, 255, 255, 0.15)) !important;
    border-radius: 50% !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    color: white !important;
    font-weight: bold !important;
    font-size: 16px !important;
    margin-right: 10px !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2) !important;
    border: 2px solid rgba(255, 255, 255, 0.3) !important;
    transition: all 0.3s ease !important;
    flex-shrink: 0 !important;
}

/* Ensure proper page layout for footer positioning */
body {
    display: flex !important;
    flex-direction: column !important;
    min-height: 100vh !important;
}

.admin-main-content {
    flex: 1 !important;
    margin-top: 90px !important;
    min-height: calc(100vh - 160px) !important;
}

/* Queue Monitor Container */
.queue-monitor-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
    min-height: calc(100vh - 200px); /* Ensure minimum height for footer positioning */
}

.queue-monitor-header {
    background: linear-gradient(135deg, var(--primary-green) 0%, var(--primary-light) 50%, var(--primary-dark) 100%);
    color: white;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-lg);
    margin-bottom: 30px;
    padding: 25px 35px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border: 1px solid rgba(255,255,255,0.1);
    backdrop-filter: blur(10px);
}

.queue-monitor-header h2 { 
    color: white; 
    font-weight: 700; 
    margin: 0; 
    font-size: 28px;
    letter-spacing: -0.5px;
    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.queue-monitor-header h2::before { 
    content: '📊'; 
    font-size: 32px; 
    margin-right: 12px; 
    filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
}

.header-actions { 
    display: flex; 
    gap: 18px; 
    align-items: center; 
}

		.refresh-indicator {
			background: rgba(255,255,255,0.15);
			color: rgba(255,255,255,0.95);
			padding: 10px 18px;
			border-radius: 25px;
			border: 1px solid rgba(255,255,255,0.25);
			font-size: 14px;
			font-weight: 500;
			backdrop-filter: blur(10px);
			transition: var(--transition);
			box-shadow: var(--shadow-sm);
		}

		.refresh-indicator:hover {
			background: rgba(255,255,255,0.2);
			transform: translateY(-1px);
		}

		.btn {
			background: rgba(255,255,255,0.15);
			color: white;
			border: 1px solid rgba(255,255,255,0.25);
			padding: 10px 20px;
			border-radius: var(--border-radius-sm);
			transition: var(--transition);
			font-weight: 500;
			font-size: 14px;
			cursor: pointer;
			backdrop-filter: blur(10px);
			box-shadow: var(--shadow-sm);
			display: inline-flex;
			align-items: center;
			gap: 8px;
		}

		.btn:hover { 
			background: rgba(255,255,255,0.25); 
			transform: translateY(-2px);
			box-shadow: var(--shadow-md);
		}

.monitor-grid { 
    display: grid; 
    grid-template-columns: 1fr 1fr; 
    gap: 25px; 
    margin-top: 25px; 
    width: 100%; 
}

		.panel {
			background: var(--bg-white);
			border-radius: var(--border-radius);
			padding: 30px;
			box-shadow: var(--shadow-sm);
			border: 1px solid var(--border-light);
			transition: var(--transition);
			position: relative;
			overflow: hidden;
		}

		.panel::before {
			content: '';
			position: absolute;
			top: 0;
			left: 0;
			right: 0;
			height: 4px;
			background: linear-gradient(90deg, var(--primary-green), var(--primary-light));
		}

		.panel:hover {
			box-shadow: var(--shadow-md);
			transform: translateY(-2px);
		}

		.panel h3 {
			color: var(--text-primary);
			font-weight: 700;
			font-size: 20px;
			border-bottom: 2px solid var(--border-light);
			padding-bottom: 15px;
			margin-bottom: 25px;
			display: flex;
			align-items: center;
			gap: 12px;
			letter-spacing: -0.5px;
		}

		.panel h3.serving-title::before { 
			content: '🎯'; 
			font-size: 24px;
			filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
		}
		
		.panel h3.waiting-title::before { 
			content: '⏳'; 
			font-size: 24px;
			filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
		}

		.stats { 
			display: grid; 
			grid-template-columns: repeat(5, 1fr); 
			gap: 20px; 
			margin-bottom: 25px; 
		}

		.stat {
			background: var(--bg-white);
			border-radius: var(--border-radius);
			padding: 25px 20px;
			text-align: center;
			box-shadow: var(--shadow-sm);
			border: 1px solid var(--border-light);
			transition: var(--transition);
			position: relative;
			overflow: hidden;
		}

		.stat::before {
			content: '';
			position: absolute;
			top: 0;
			left: 0;
			right: 0;
			height: 4px;
			background: var(--warning-orange);
			transition: var(--transition);
		}

		.stat:hover { 
			transform: translateY(-3px); 
			box-shadow: var(--shadow-md);
		}
		
		.stat:nth-child(1)::before { background: linear-gradient(90deg, var(--warning-orange), #ffb74d); }
		.stat:nth-child(2)::before { background: linear-gradient(90deg, var(--success-green), #81c784); }
		.stat:nth-child(3)::before { background: linear-gradient(90deg, var(--info-blue), #64b5f6); }
		.stat:nth-child(4)::before { background: linear-gradient(90deg, var(--danger-red), #e57373); }
		.stat:nth-child(5)::before { background: linear-gradient(90deg, var(--primary-green), var(--primary-light)); }

		.stat .num { 
			font-size: 32px; 
			font-weight: 800; 
			color: var(--text-primary); 
			margin-bottom: 8px; 
			line-height: 1;
			letter-spacing: -1px;
		}
		
		.stat div:last-child { 
			font-size: 13px; 
			font-weight: 600; 
			color: var(--text-muted); 
			text-transform: uppercase; 
			letter-spacing: 0.5px;
			margin-top: 4px;
		}

		.serving-list, .waiting-list { 
			max-height: 65vh; 
			overflow-y: auto; 
			padding-right: 8px;
		}

		.serving-list::-webkit-scrollbar, .waiting-list::-webkit-scrollbar {
			width: 6px;
		}

		.serving-list::-webkit-scrollbar-track, .waiting-list::-webkit-scrollbar-track {
			background: var(--bg-light);
			border-radius: 3px;
		}

		.serving-list::-webkit-scrollbar-thumb, .waiting-list::-webkit-scrollbar-thumb {
			background: var(--border-light);
			border-radius: 3px;
		}

		.serving-list::-webkit-scrollbar-thumb:hover, .waiting-list::-webkit-scrollbar-thumb:hover {
			background: var(--text-muted);
		}

		.ticket {
			display: flex;
			justify-content: space-between;
			align-items: center;
			background: var(--bg-light);
			padding: 20px;
			border-radius: var(--border-radius-sm);
			margin-bottom: 12px;
			border-left: 4px solid var(--primary-green);
			transition: var(--transition);
			border: 1px solid rgba(46, 125, 50, 0.1);
			position: relative;
			overflow: hidden;
		}

		.ticket::before {
			content: '';
			position: absolute;
			top: 0;
			left: 0;
			bottom: 0;
			width: 4px;
			background: linear-gradient(180deg, var(--primary-green), var(--primary-light));
		}

		.ticket:hover { 
			transform: translateX(4px); 
			background: var(--bg-white); 
			box-shadow: var(--shadow-sm);
			border-color: rgba(46, 125, 50, 0.2);
		}

		.ticket-number { 
			font-size: 18px; 
			font-weight: 800; 
			color: var(--text-primary); 
			letter-spacing: -0.5px;
			margin-bottom: 4px;
		}
		
		.ticket-service { 
			font-size: 15px; 
			color: var(--primary-green); 
			font-weight: 600; 
			margin: 6px 0; 
		}
		
		.ticket-details { 
			font-size: 13px; 
			color: var(--text-secondary); 
			display: flex; 
			gap: 12px; 
			flex-wrap: wrap; 
			align-items: center;
			margin-top: 8px;
		}

		.ticket-details i {
			color: var(--text-muted);
			width: 14px;
			text-align: center;
		}

		.badge {
			padding: 6px 14px;
			border-radius: 20px;
			font-size: 12px;
			font-weight: 600;
			text-transform: uppercase;
			display: inline-flex;
			align-items: center;
			gap: 6px;
			letter-spacing: 0.5px;
			transition: var(--transition);
			box-shadow: var(--shadow-sm);
			border: 1px solid transparent;
		}

		.badge.serving { 
			background: linear-gradient(135deg, #e8f5e8, #c8e6c9); 
			color: var(--success-green); 
			border-color: rgba(76, 175, 80, 0.2);
		}
		
		.badge.waiting { 
			background: linear-gradient(135deg, #fff3e0, #ffcc02); 
			color: #e65100; 
			border-color: rgba(255, 152, 0, 0.2);
		}
		
		.badge.urgent { 
			background: linear-gradient(135deg, #ffebee, #ef9a9a); 
			color: var(--danger-red); 
			border-color: rgba(244, 67, 54, 0.2);
			animation: pulse 2s infinite;
		}
		
		.badge.priority { 
			background: linear-gradient(135deg, #e3f2fd, #90caf9); 
			color: var(--info-blue); 
			border-color: rgba(33, 150, 243, 0.2);
		}

		@keyframes pulse {
			0%, 100% { transform: scale(1); }
			50% { transform: scale(1.05); }
		}

		.empty-state {
			text-align: center;
			padding: 50px 25px;
			color: var(--text-muted);
			background: linear-gradient(135deg, var(--bg-light), var(--bg-white));
			border: 2px dashed var(--border-light);
			border-radius: var(--border-radius);
			transition: var(--transition);
			position: relative;
			overflow: hidden;
		}

		.empty-state::before {
			content: '';
			position: absolute;
			top: 0;
			left: -100%;
			width: 100%;
			height: 100%;
			background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
			animation: shimmer 3s infinite;
		}

		@keyframes shimmer {
			0% { left: -100%; }
			100% { left: 100%; }
		}

		.empty-state:hover {
			border-color: var(--primary-green);
			color: var(--text-secondary);
			transform: translateY(-2px);
		}

		.empty-state i {
			font-size: 48px;
			margin-bottom: 15px;
			color: var(--text-muted);
			opacity: 0.7;
		}

		.empty-state div {
			font-size: 16px;
			font-weight: 500;
			margin-top: 10px;
		}

		.error-state {
			text-align: center;
			padding: 40px 25px;
			color: var(--danger-red);
			background: linear-gradient(135deg, #ffebee, #fce4ec);
			border: 2px solid #ffcdd2;
			border-radius: var(--border-radius);
			position: relative;
			overflow: hidden;
		}

		.error-state::before {
			content: '';
			position: absolute;
			top: 0;
			left: 0;
			right: 0;
			height: 4px;
			background: linear-gradient(90deg, var(--danger-red), #e57373);
		}

		.error-state i {
			font-size: 42px;
			margin-bottom: 12px;
			color: var(--danger-red);
			animation: shake 0.5s ease-in-out infinite alternate;
		}

		@keyframes shake {
			0% { transform: translateX(0); }
			100% { transform: translateX(2px); }
		}

		/* Responsive Design - Enhanced */
		@media (max-width: 768px) {
			.monitor-grid { 
				grid-template-columns: 1fr; 
				gap: 20px; 
			}
			
			.stats { 
				grid-template-columns: repeat(2, 1fr); 
				gap: 15px; 
			}
			
			.queue-monitor-header { 
				flex-direction: column; 
				text-align: center; 
				gap: 20px;
				padding: 20px 25px;
			}
			
			.queue-monitor-header h2 {
				font-size: 24px;
			}
			
			.header-actions { 
				justify-content: center;
				flex-wrap: wrap;
				gap: 15px;
			}
			
			.queue-monitor-container { 
				padding: 15px; 
				min-height: calc(100vh - 180px);
			}
			
			.panel { 
				padding: 25px 20px; 
			}
			
			.panel h3 {
				font-size: 18px;
			}
			
			.ticket {
				padding: 18px;
				flex-direction: column;
				align-items: flex-start;
				gap: 12px;
			}
			
			.ticket-details {
				flex-direction: column;
				gap: 8px;
				align-items: flex-start;
				width: 100%;
			}
			
			.stat .num {
				font-size: 28px;
			}
			
			/* Fix admin header text stacking on mobile */
			.admin-brand-text {
				display: flex;
				flex-direction: column;
				align-items: flex-start;
				line-height: 1.2;
			}
			
			.admin-brand-text h1 {
				font-size: 16px !important;
				margin-bottom: 2px !important;
				white-space: nowrap;
			}
			
			.admin-brand-text p {
				font-size: 10px !important;
				white-space: nowrap;
				margin: 0 !important;
			}
			
			.admin-brand {
				min-width: 180px !important;
				padding: 8px 12px !important;
			}
			
			.admin-brand-logo {
				width: 32px !important;
				height: 32px !important;
				margin-right: 8px !important;
				flex-shrink: 0;
			}
		}

@media (max-width: 480px) {
    .stats { 
        grid-template-columns: 1fr; 
        gap: 12px;
    }
    
    .queue-monitor-container { 
        padding: 12px;
        min-height: calc(100vh - 160px);
    }
    
    .queue-monitor-header { 
        margin-bottom: 20px;
        padding: 18px 20px;
    }
    
    .queue-monitor-header h2 {
        font-size: 22px;
    }
    
    .panel {
        padding: 20px 15px;
    }
    
    .stat {
        padding: 20px 15px;
    }
    
    .stat .num {
        font-size: 26px;
    }
    
    .ticket {
        padding: 15px;
    }
    
    .ticket-number {
        font-size: 16px;
    }
    
    .monitor-grid {
        gap: 15px;
    }
    
    /* Further optimize admin header for very small screens */
    .admin-brand-text h1 {
        font-size: 14px !important;
    }
    
    .admin-brand-text p {
        font-size: 9px !important;
    }
    
    .admin-brand {
        min-width: 160px !important;
        padding: 6px 10px !important;
    }
    
    .admin-brand-logo {
        width: 28px !important;
        height: 28px !important;
        margin-right: 6px !important;
    }
    
    .admin-navbar {
        height: auto !important;
        min-height: 60px;
    }
    
    .admin-navbar-container {
        height: auto !important;
        min-height: 60px;
        padding: 8px 10px !important;
    }
}
</style>

<div class="queue-monitor-container">
    <header class="queue-monitor-header">
        <h2>Queue Live Monitor</h2>
        <div class="header-actions">
            <span class="refresh-indicator" id="refreshTime">—</span>
            <button id="manualRefresh" class="btn btn-secondary">Refresh</button>
        </div>
    </header>

    <div class="panel">
        <div class="stats" id="stats"></div>
    </div>
    <div class="monitor-grid">
        <div class="panel">
            <h3 class="serving-title">Currently Serving / Ready for Pick-up</h3>
            <div class="serving-list" id="servingList"></div>
        </div>
        <div class="panel">
            <h3 class="waiting-title">Waiting Queue</h3>
            <div class="waiting-list" id="waitingList"></div>
        </div>
    </div>
</div>

	<script>
	// Ensure base path is properly set
	const basePath = '../';
	const apiUrl = basePath + 'api/queue-state.php';
	const statsEl = document.getElementById('stats');
	const servingEl = document.getElementById('servingList');
	const waitingEl = document.getElementById('waitingList');
	const refreshEl = document.getElementById('refreshTime');
	let retryCount = 0;
	let maxRetries = 3;
	let isOnline = true;
	let refreshInterval;

	// Debug logging function
	function debugLog(message, data = null) {
		console.log('[Queue Monitor]', message, data);
	}

	// Initialize debug mode
	debugLog('Queue monitor initialized', { apiUrl: apiUrl, basePath: basePath });

	function render() {
		debugLog('Starting render request');
		
		// Show loading state
		refreshEl.innerHTML = '<i class="fas fa-sync fa-spin"></i> Updating...';
		refreshEl.style.color = 'rgba(255,255,255,0.8)';
		
		fetch(apiUrl, { 
			credentials: 'same-origin',
			cache: 'no-cache',
			headers: {
				'Content-Type': 'application/json',
				'X-Requested-With': 'XMLHttpRequest'
			}
		})
			.then(r => {
				debugLog('Response received', { status: r.status, statusText: r.statusText });
				if (!r.ok) {
					throw new Error(`HTTP ${r.status}: ${r.statusText}`);
				}
				return r.text(); // Get text first for debugging
			})
			.then(text => {
				debugLog('Response text', text.substring(0, 200) + '...');
				try {
					return JSON.parse(text);
				} catch (e) {
					debugLog('JSON parse error', { text: text, error: e.message });
					throw new Error('Invalid JSON response: ' + e.message);
				}
			})
			.then(data => {
				debugLog('Parsed data', data);
				if (!data.success) {
					throw new Error(data.message || 'API returned error');
				}
				
				// Reset retry count on success
				retryCount = 0;
				isOnline = true;
				
				refreshEl.innerHTML = `<i class="fas fa-check-circle"></i> Updated ${new Date().toLocaleTimeString()}`;
				refreshEl.style.color = 'rgba(255,255,255,0.8)';
				
				// Fade out the success indicator after 2 seconds
				setTimeout(() => {
					if (refreshEl.innerHTML.includes('Updated')) {
						refreshEl.innerHTML = `<i class="fas fa-clock"></i> Last: ${new Date().toLocaleTimeString()}`;
					}
				}, 2000);

				// Stats with fallback
				const s = data.stats || {};
				debugLog('Rendering stats', s);
				statsEl.innerHTML = `
					<div class="stat"><div class="num">${s.waiting || 0}</div><div>Waiting</div></div>
					<div class="stat"><div class="num">${s.serving || 0}</div><div>Serving</div></div>
					<div class="stat"><div class="num">${s.completed || 0}</div><div>Completed</div></div>
					<div class="stat"><div class="num">${s.cancelled || 0}</div><div>Cancelled</div></div>
					<div class="stat"><div class="num">${s.total || 0}</div><div>Total</div></div>
				`;

				// Serving list (also includes ready-for-pickup)
				debugLog('Rendering serving list', { count: data.serving ? data.serving.length : 0 });
				if (data.serving && data.serving.length > 0) {
					servingEl.innerHTML = data.serving.map(t => `
						<div class="ticket">
							<div class="ticket-info">
								<div class="ticket-number">${escapeHtml(t.ticket_number)}</div>
								<div class="ticket-service">${escapeHtml(t.service_name || '')}</div>
								<div class="ticket-details">
									<i class="fas fa-user"></i>
									<span>${escapeHtml(t.customer_name || '')}</span>
								</div>
							</div>
							<div>
								<span class="badge serving">
									<i class="fas fa-${t.is_ready_pickup ? 'check-circle' : 'clock'}"></i>
									${t.is_ready_pickup ? 'Ready for pick-up' : escapeHtml(t.window_number || t.window_name || 'Serving')}
								</span>
							</div>
						</div>
					`).join('');
				} else {
					servingEl.innerHTML = '<div class="empty-state"><i class="fas fa-coffee"></i><div>No tickets currently being served</div></div>';
				}

				// Waiting list
				debugLog('Rendering waiting list', { count: data.waiting ? data.waiting.length : 0 });
				if (data.waiting && data.waiting.length > 0) {
					waitingEl.innerHTML = data.waiting.map(t => `
						<div class="ticket">
							<div class="ticket-info">
								<div class="ticket-number">${escapeHtml(t.ticket_number)}</div>
								<div class="ticket-service">${escapeHtml(t.service_name || '')}</div>
								<div class="ticket-details">
									<i class="fas fa-user"></i>
									<span>${escapeHtml(t.customer_name || '')}</span>
									<i class="fas fa-clock"></i>
									<span>ETA ${formatTime(t.estimated_time)}</span>
									<i class="fas fa-list-ol"></i>
									<span>#${t.queue_position ?? '-'}</span>
								</div>
							</div>
							<div>
								<span class="badge waiting">
									<i class="fas fa-hourglass-half"></i>
									Waiting
								</span>
								${badgeForPriority(t.priority_level)}
							</div>
						</div>
					`).slice(0, 50).join('');
				} else {
					waitingEl.innerHTML = '<div class="empty-state"><i class="fas fa-list"></i><div>No tickets waiting in queue</div></div>';
				}
			})
			.catch(err => {
				debugLog('Error caught', { error: err.message, retryCount: retryCount });
				console.error('Queue monitor error:', err);
				console.log('Current session check:', document.cookie);
				console.log('Full error details:', err);
				retryCount++;
				
				// Update UI to show error state
				if (retryCount <= maxRetries) {
					refreshEl.textContent = `Retrying... (${retryCount}/${maxRetries}) - ${err.message.substring(0, 30)}...`;
					refreshEl.style.color = '#856404';
					
					debugLog('Scheduling retry', { delay: 2000 * retryCount });
					// Retry after delay
					setTimeout(() => render(), 2000 * retryCount);
				} else {
					// Max retries reached
					isOnline = false;
					refreshEl.textContent = `Connection failed: ${err.message.substring(0, 30)}...`;
					refreshEl.style.color = '#dc3545';
					
					debugLog('Max retries reached, showing error state');
					// Show error state in UI
					if (statsEl.innerHTML.indexOf('Connection Error') === -1) {
						statsEl.innerHTML = `
							<div class="error-state" style="grid-column: 1 / -1;">
								<i class="fas fa-exclamation-triangle"></i>
								<div style="font-size: 18px; font-weight: 600; margin: 16px 0 8px;">Connection Error</div>
								<small style="display: block; margin-bottom: 16px;">${escapeHtml(err.message)}</small>
								<button onclick="manualRefresh()" class="btn" style="background: var(--primary-red); color: white; border: none;">
									<i class="fas fa-redo"></i> Retry Connection
								</button>
							</div>
						`;
						servingEl.innerHTML = '<div class="error-state"><i class="fas fa-wifi"></i><div>Unable to load serving data</div></div>';
						waitingEl.innerHTML = '<div class="error-state"><i class="fas fa-wifi"></i><div>Unable to load waiting data</div></div>';
					}
				}
			});
	}

	function escapeHtml(s) {
		if (s == null) return '';
		return String(s).replace(/[&<>"']/g, function(c) {
			return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#x27;'}[c] || c;
		});
	}

	function badgeForPriority(level) {
		if (level === 'urgent') return '<span class="badge urgent"><i class="fas fa-exclamation-triangle"></i> Urgent</span>';
		if (level === 'priority') return '<span class="badge priority"><i class="fas fa-star"></i> Priority</span>';
		if (level === 'senior') return '<span class="badge priority"><i class="fas fa-user-plus"></i> Senior</span>';
		if (level === 'pwd') return '<span class="badge priority"><i class="fas fa-wheelchair"></i> PWD</span>';
		if (level === 'pregnant') return '<span class="badge priority"><i class="fas fa-baby"></i> Pregnant</span>';
		return '';
	}

	function formatTime(dt) {
		if (!dt) return '-';
		try { 
			const date = new Date(dt);
			if (isNaN(date.getTime())) return dt;
			return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }); 
		} catch(_) { 
			return dt; 
		}
	}

	// Manual refresh function that resets retry counter
	function manualRefresh() {
		debugLog('Manual refresh triggered');
		retryCount = 0;
		isOnline = true;
		refreshEl.innerHTML = '<i class="fas fa-sync fa-spin"></i> Refreshing...';
		refreshEl.style.color = 'rgba(255,255,255,0.8)';
		render();
	}

	// Initial load with DOM ready check
	function initializeMonitor() {
		// Validate DOM elements exist
		if (!statsEl || !servingEl || !waitingEl || !refreshEl) {
			console.error('Required DOM elements not found');
			return;
		}
		
		debugLog('Starting initial load');
		render();
		
		// Manual refresh button
		const manualRefreshBtn = document.getElementById('manualRefresh');
		if (manualRefreshBtn) {
			manualRefreshBtn.addEventListener('click', manualRefresh);
		}
		
		// Auto-refresh every 10 seconds, but only if online
		refreshInterval = setInterval(() => {
			debugLog('Auto-refresh check', { isOnline: isOnline, retryCount: retryCount });
			if (isOnline && retryCount === 0) {
				render();
			}
		}, 10000);
		
		// Add visibility change handler to pause/resume auto-refresh
		document.addEventListener('visibilitychange', function() {
			if (document.hidden) {
				debugLog('Page hidden, clearing refresh interval');
				if (refreshInterval) {
					clearInterval(refreshInterval);
				}
			} else {
				debugLog('Page visible, restarting refresh interval');
				if (refreshInterval) {
					clearInterval(refreshInterval);
				}
				refreshInterval = setInterval(() => {
					if (isOnline && retryCount === 0) {
						render();
					}
				}, 10000);
				// Immediate refresh when page becomes visible
				if (isOnline) {
					render();
				}
			}
		});
	}
	
	// Initialize when DOM is ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initializeMonitor);
	} else {
		initializeMonitor();
	}
	</script>
</div>

<?php include '../includes/admin_footer.php'; ?>


