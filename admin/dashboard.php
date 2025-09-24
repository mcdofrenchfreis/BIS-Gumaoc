<?php
session_start();
include '../includes/db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Get form submission counts
try {
    // Check and create tables if they don't exist
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS services (
            id INT PRIMARY KEY AUTO_INCREMENT,
            title VARCHAR(255) NOT NULL,
            description TEXT NOT NULL,
            button_text VARCHAR(100) NOT NULL,
            button_link VARCHAR(255) NOT NULL,
            is_featured BOOLEAN DEFAULT FALSE,
            features TEXT,
            display_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS updates (
            id INT PRIMARY KEY AUTO_INCREMENT,
            title VARCHAR(255) NOT NULL,
            description TEXT NOT NULL,
            badge_text VARCHAR(50) NOT NULL,
            badge_type ENUM('important', 'new', 'community', 'info') DEFAULT 'info',
            date VARCHAR(50) NOT NULL,
            status VARCHAR(50) NOT NULL,
            is_priority BOOLEAN DEFAULT FALSE,
            display_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");
    
    // Get form submission counts (with error handling for non-existent tables)
    $resident_count = 0;
    $certificate_count = 0;
    $business_count = 0;
    $pending_resident = 0;
    $pending_certificate = 0;
    $pending_business = 0;
    
    try {
        $resident_count = $pdo->query("SELECT COUNT(*) FROM resident_registrations")->fetchColumn();
        $pending_resident = $pdo->query("SELECT COUNT(*) FROM resident_registrations WHERE status = 'pending'")->fetchColumn();
    } catch (Exception $e) {
        // Table doesn't exist yet
    }
    
    try {
        $certificate_count = $pdo->query("SELECT COUNT(*) FROM certificate_requests")->fetchColumn();
        $pending_certificate = $pdo->query("SELECT COUNT(*) FROM certificate_requests WHERE status = 'pending'")->fetchColumn();
    } catch (Exception $e) {
        // Table doesn't exist yet
    }
    
    try {
        $business_count = $pdo->query("SELECT COUNT(*) FROM business_applications")->fetchColumn();
        $pending_business = $pdo->query("SELECT COUNT(*) FROM business_applications WHERE status = 'pending'")->fetchColumn();
    } catch (Exception $e) {
        // Table doesn't exist yet
    }
    
    // Get services and updates counts
    $services_count = $pdo->query("SELECT COUNT(*) FROM services")->fetchColumn();
    $updates_count = $pdo->query("SELECT COUNT(*) FROM updates")->fetchColumn();
    
    // Get RFID statistics
    $rfid_available = 0;
    $rfid_assigned = 0;
    try {
        $rfid_available = $pdo->query("SELECT COUNT(*) FROM scanned_rfid_codes WHERE status = 'available'")->fetchColumn();
        $rfid_assigned = $pdo->query("SELECT COUNT(*) FROM scanned_rfid_codes WHERE status = 'assigned'")->fetchColumn();
    } catch (Exception $e) {
        // Table doesn't exist yet
    }
    
} catch (Exception $e) {
    $error_message = "Database error: " . $e->getMessage();
    // Set default values if there's an error
    $resident_count = 0;
    $certificate_count = 0;
    $business_count = 0;
    $pending_resident = 0;
    $pending_certificate = 0;
    $pending_business = 0;
    $services_count = 0;
    $updates_count = 0;
    $rfid_available = 0;
    $rfid_assigned = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Barangay Gumaoc East</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: #f8faf8;
            background-attachment: fixed;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            position: relative;
            color: #333;
        }
        
        /* Add subtle pattern overlay */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23006400' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            pointer-events: none;
            z-index: -1;
        }
        
        .admin-dashboard {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        /* Header */
        .dashboard-header {
            background: linear-gradient(135deg, rgba(0, 100, 0, 0.95) 0%, rgba(34, 139, 34, 0.95) 100%);
            backdrop-filter: blur(10px);
            color: white;
            padding: 2rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }
        
        .dashboard-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23ffffff' fill-opacity='0.05' fill-rule='evenodd'/%3E%3C/svg%3E");
            opacity: 0.1;
        }
        
        .dashboard-header {
            display: flex;
            align-items: center;
            text-align: left;
            padding: 1.5rem 2rem;
        }
        
        .gov-seal {
            margin-right: 1.5rem;
        }
        
        .gov-logo {
            width: 80px;
            height: 80px;
        }
        
        .gov-header-text {
            flex: 1;
        }
        
        .gov-header-top {
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        
        .dashboard-header h1 {
            margin: 0 0 0.5rem 0;
            font-size: 1.8rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            position: relative;
            z-index: 1;
        }
        
        .dashboard-header p {
            font-size: 1rem;
            opacity: 0.9;
            position: relative;
            z-index: 1;
            font-weight: 400;
        }
        
        @media (max-width: 768px) {
            .dashboard-header {
                flex-direction: column;
                text-align: center;
                padding: 1.5rem 1rem;
            }
            
            .gov-seal {
                margin-right: 0;
                margin-bottom: 1rem;
            }
            
            .gov-logo {
                width: 60px;
                height: 60px;
            }
        }
        
        /* Navigation */
        .admin-nav {
            background: #fff;
            padding: 0.75rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border: 1px solid #e0e0e0;
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-start;
            gap: 0.25rem;
        }
        
        .admin-nav a {
            color: #006400;
            text-decoration: none;
            font-weight: 500;
            padding: 0.6rem 1rem;
            border-radius: 4px;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            background-color: transparent;
            border-bottom: 2px solid transparent;
        }
        
        .admin-nav a:hover {
            color: #fff;
            background-color: #228B22;
            border-bottom: 2px solid #006400;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 100, 0, 0.1);
        }
        
        /* Stats Grid - Simplified */
        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.2rem 1rem;
            border-radius: 8px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
            border-left: 4px solid #228B22;
            text-align: left;
            transition: transform 0.2s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card h3 {
            color: #555;
            margin: 0 0 0.5rem 0;
            font-size: 0.9rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .stat-number {
            font-size: 1.8rem;
            font-weight: 600;
            color: #006400;
            margin-bottom: 0.3rem;
        }
        
        .stat-pending {
            font-size: 0.8rem;
            color: #d14836;
            font-weight: 500;
            background: rgba(209, 72, 54, 0.1);
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
            display: inline-block;
        }

        /* Main Content Grid */
        .dashboard-main {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .dashboard-section {
            background: #ffffff;
            border-radius: 8px;
            padding: 1.75rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            border: 1px solid #e8f0e8;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .dashboard-section:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }
        
        .section-title {
            color: #006400;
            font-size: 1.25rem;
            margin-bottom: 1.5rem;
            font-weight: 600;
            padding-bottom: 0.8rem;
            border-bottom: 2px solid #e8f0e8;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .section-icon {
            background: #228B22;
            color: white;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            box-shadow: 0 4px 8px rgba(0, 100, 0, 0.2);
        }
        
        /* Action Cards - Enhanced */
        .dashboard-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
        }
        
        .action-card {
            background: white;
            padding: 1.8rem;
            border-radius: 8px;
            text-align: left;
            transition: all 0.3s ease;
            border: 1px solid #e8f0e8;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            position: relative;
            overflow: hidden;
        }
        
        .action-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, #006400, #228B22);
        }
        
        .action-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            border-color: #228B22;
        }
        
        .action-icon {
            font-size: 2.2rem;
            margin-bottom: 1rem;
            color: #228B22;
            position: relative;
            z-index: 1;
        }
        
        .action-card h3 {
            color: #333;
            margin-bottom: 1rem;
            font-size: 1.2rem;
            font-weight: 600;
            position: relative;
            z-index: 1;
        }
        
        .action-card p {
            color: #555;
            margin-bottom: 1.5rem;
            line-height: 1.6;
            font-size: 0.95rem;
            position: relative;
            z-index: 1;
        }
        
        .admin-btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: #228B22;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 4px 8px rgba(0, 100, 0, 0.15);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.85rem;
            position: relative;
            z-index: 1;
            border: none;
        }
        
        .admin-btn:hover {
            background: #006400;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 100, 0, 0.2);
            text-decoration: none;
            color: white;
        }
        
        /* Alert Styles */
        .alert {
            padding: 1.2rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            border: 1px solid transparent;
            font-weight: 400;
        }
        
        .alert-error {
            background: #fff6f6;
            color: #9f3a38;
            border-color: #e0b4b4;
        }
        
        .alert-success {
            background: #f1f9f1;
            color: #2c662d;
            border-color: #a3c293;
        }
        
        /* Quick Stats Row */
        .quick-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.2rem;
            margin-bottom: 2rem;
        }
        
        .quick-stat {
            background: #ffffff;
            padding: 1.5rem;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            border: 1px solid #e8f0e8;
            transition: transform 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .quick-stat::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, #006400, #228B22);
        }
        
        .quick-stat:hover {
            transform: translateY(-5px);
        }
        
        .quick-stat-number {
            font-size: 2rem;
            font-weight: 600;
            color: #006400;
        }
        
        .quick-stat-label {
            color: #555;
            font-size: 0.9rem;
            margin-top: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 500;
        }
        
        /* Responsive Design - Optimized for mobile experience */
        @media (max-width: 1400px) {
            .dashboard-stats {
                gap: 1rem;
            }
            
            .stat-card {
                padding: 1.2rem;
                min-width: 180px;
            }
            
            .stat-card h3 {
                font-size: 0.9rem;
            }
            
            .stat-number {
                font-size: 2.2rem;
            }
            
            .stat-pending {
                font-size: 0.8rem;
                padding: 0.25rem 0.5rem;
            }
        }
        
        @media (max-width: 1200px) {
            .dashboard-stats {
                grid-template-columns: repeat(3, minmax(160px, 1fr));
                gap: 0.8rem;
            }
            
            .stat-card {
                padding: 1rem;
                min-width: 160px;
            }
            
            .stat-card h3 {
                font-size: 0.85rem;
                margin-bottom: 0.8rem;
            }
            
            .stat-number {
                font-size: 2rem;
            }
            
            .dashboard-actions {
                grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
                gap: 1.2rem;
            }
            
            .dashboard-main {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .admin-dashboard {
                padding: 1rem;
            }
            
            .dashboard-header {
                padding: 1.8rem 1.5rem;
                border-radius: 6px;
            }
            
            .dashboard-header h1 {
                font-size: 1.8rem;
            }
            
            .dashboard-stats {
                grid-template-columns: repeat(2, minmax(140px, 1fr));
                gap: 0.8rem;
                overflow-x: auto;
                padding-bottom: 0.5rem;
            }
            
            .stat-card {
                padding: 1rem;
                min-width: 140px;
                border-radius: 6px;
            }
            
            .stat-card h3 {
                font-size: 0.75rem;
                margin-bottom: 0.6rem;
            }
            
            .stat-number {
                font-size: 1.8rem;
            }
            
            .stat-pending {
                font-size: 0.7rem;
                padding: 0.2rem 0.4rem;
            }
            
            .dashboard-actions {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .action-card {
                padding: 1.5rem;
            }
            
            .admin-nav {
                flex-direction: column;
                border-radius: 6px;
            }
            
            .admin-nav a {
                display: block;
                width: 100%;
                margin: 0.25rem 0;
                text-align: center;
                border-radius: 4px;
            }
            
            .dashboard-section {
                padding: 1.5rem;
                border-radius: 6px;
            }
            
            .section-title {
                font-size: 1.1rem;
            }
        }
        
        @media (max-width: 480px) {
            .dashboard-header h1 {
                font-size: 1.5rem;
            }
            
            .dashboard-header p {
                font-size: 0.9rem;
            }
            
            .dashboard-stats {
                grid-template-columns: 1fr;
                gap: 0.6rem;
            }
            
            .stat-card {
                padding: 0.8rem;
                min-width: 120px;
            }
            
            .stat-card h3 {
                font-size: 0.7rem;
            }
            
            .stat-number {
                font-size: 1.5rem;
            }
            
            .stat-pending {
                font-size: 0.65rem;
            }
            
            .action-icon {
                font-size: 2rem;
            }
            
            .admin-btn {
                padding: 0.7rem 1.2rem;
                font-size: 0.8rem;
            }
            
            .quick-stats {
                grid-template-columns: 1fr;
            }
        }

        /* Queue Monitor (Dashboard preview) - Enhanced */
        .queue-section .queue-stats { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); 
            gap: 1rem; 
            margin-bottom: 1.5rem; 
        }
        
        .queue-section .qstat { 
            background: #f8faf8; 
            border: 1px solid #e8f0e8; 
            border-radius: 8px; 
            padding: 1rem; 
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
        }
        
        .queue-section .qstat:hover {
            transform: translateY(-5px);
        }
        
        .queue-section .qnum { 
            font-size: 1.6rem; 
            font-weight: 600; 
            color: #006400; 
        }
        
        .queue-section .qlabel { 
            font-size: 0.85rem; 
            color: #555; 
            text-transform: uppercase; 
            letter-spacing: 0.5px;
            margin-top: 0.5rem;
            font-weight: 500;
        }
        
        .queue-lists { 
            display: grid; 
            grid-template-columns: 2fr 1fr; 
            gap: 1.5rem; 
        }
        
        .queue-panel { 
            background: #ffffff; 
            border: 1px solid #e8f0e8; 
            border-radius: 8px; 
            padding: 1.5rem; 
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        
        .queue-panel h3 { 
            margin: 0 0 1rem 0; 
            color: #006400; 
            font-size: 1.1rem; 
            font-weight: 600; 
            text-transform: uppercase; 
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e8f0e8;
            padding-bottom: 0.8rem;
        }
        
        .queue-list { 
            max-height: 350px; 
            overflow-y: auto; 
            display: grid; 
            gap: 0.8rem; 
        }
        
        .queue-ticket { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            background: #f8faf8; 
            border: 1px solid #e8f0e8; 
            padding: 0.8rem 1rem; 
            border-radius: 6px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .queue-ticket:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
            border-color: #228B22;
        }
        
        .qbadges { 
            display: flex; 
            gap: 8px; 
            align-items: center; 
        }
        
        .qbadge { 
            padding: 3px 10px; 
            border-radius: 4px; 
            font-size: 12px; 
            font-weight: 600; 
            border: 1px solid transparent;
            background: #e8f0e8;
            color: #006400;
        }
        
        .qbadge.urgent {
            background: #fff0f0;
            color: #d14836;
        }
        
        .qbadge.new {
            background: #f0f8ff;
            color: #0066cc;
        }
        
        @media (max-width: 992px) {
            .queue-lists {
                grid-template-columns: 1fr;
            }
            
            .queue-section .queue-stats {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        
        @media (max-width: 576px) {
            .queue-section .queue-stats {
                grid-template-columns: repeat(2, 1fr);
            }
        }sparent; }
        .qbadge.waiting { background:#fff8e1; color:#856404; border-color: #ffe082; }
        .qbadge.serving { background:#e8f5e9; color:#1b5e20; border-color: #a5d6a7; }
        .qbadge.urgent { background:#ffebee; color:#b71c1c; border-color: #ef9a9a; }
        .qbadge.priority { background:#e3f2fd; color:#0d47a1; border-color: #90caf9; }
        .queue-actions { display:flex; justify-content: flex-end; margin-top: 0.75rem; }
        .queue-link { display:inline-block; padding: 0.6rem 1rem; background: #228B22; color:#fff; text-decoration:none; border-radius: 4px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; font-size: 0.85rem; box-shadow: 0 4px 8px rgba(0, 100, 0, 0.15); transition: all 0.3s ease; }

        @media (max-width: 768px) {
            .queue-section .queue-stats { grid-template-columns: repeat(5, minmax(80px, 1fr)); }
            .queue-lists { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="admin-dashboard">
        <div class="dashboard-header">
            <div class="gov-seal">
                <img src="../assets/images/ph-seal.svg" alt="Republic of the Philippines" class="gov-logo" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAxMDAgMTAwIj48Y2lyY2xlIGN4PSI1MCIgY3k9IjUwIiByPSI0NSIgZmlsbD0ibm9uZSIgc3Ryb2tlPSIjZmZmIiBzdHJva2Utd2lkdGg9IjIiLz48dGV4dCB4PSI1MCIgeT0iNTAiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxMCIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9IjAuMzVlbSIgZmlsbD0iI2ZmZiI+UmVwdWJsaWMgb2YgdGhlPC90ZXh0Pjx0ZXh0IHg9IjUwIiB5PSI2NSIgZm9udC1mYW1pbHk9IkFyaWFsIiBmb250LXNpemU9IjEwIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBkeT0iMC4zNWVtIiBmaWxsPSIjZmZmIj5QaGlsaXBwaW5lczwvdGV4dD48L3N2Zz4='">
            </div>
            <div class="gov-header-text">
                <div class="gov-header-top">Republic of the Philippines</div>
                <h1>Barangay Gumaoc East Admin Portal</h1>
                <p>Official Management System for Barangay Services and Records</p>
                <div class="header-actions">
                    <button class="header-btn">
                        <i class="fas fa-bell"></i> Notifications
                    </button>
                    <button class="header-btn">
                        <i class="fas fa-cog"></i> Settings
                    </button>
                </div>
            </div>
        </div>
        
        <div class="admin-nav">
            <a href="forms-manager.php">📋 Forms Manager</a>
            <a href="rfid-scanner.php">📱 RFID Scanner</a>
            <a href="manage-blotter.php">📝 Blotter Management</a>
            <a href="captain-clearances.php">🛡️ Captain Clearances</a>
            <a href="blotter-reports.php">📊 Blotter Reports</a>
            <a href="queue-monitor.php">📺 Queue Monitor</a>
            <a href="../index.php" target="_blank">🌐 View Website</a>
            <a href="logout.php">🚪 Logout</a>
        </div>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-error">
                <strong>⚠️ Error:</strong> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <div class="dashboard-stats">
            <div class="stat-card">
                <h3>👥 Census Registrations</h3>
                <div class="stat-number"><?php echo $resident_count; ?></div>
                <div class="stat-pending"><?php echo $pending_resident; ?> pending review</div>
            </div>
            
            <div class="stat-card">
                <h3>📄 Certificate Requests</h3>
                <div class="stat-number"><?php echo $certificate_count; ?></div>
                <div class="stat-pending"><?php echo $pending_certificate; ?> pending approval</div>
            </div>
            
            <div class="stat-card">
                <h3>🏢 Business Applications</h3>
                <div class="stat-number"><?php echo $business_count; ?></div>
                <div class="stat-pending"><?php echo $pending_business; ?> pending processing</div>
            </div>
            
            <div class="stat-card">
                <h3>📱 Available RFID Codes</h3>
                <div class="stat-number"><?php echo $rfid_available; ?></div>
                <div class="stat-pending">Ready for assignment</div>
            </div>
            
            <div class="stat-card">
                <h3>🆔 Assigned RFID Codes</h3>
                <div class="stat-number"><?php echo $rfid_assigned; ?></div>
                <div class="stat-pending">Currently in use</div>
            </div>
        </div>
        
        <div class="dashboard-main">
            <div class="dashboard-section">
                <h2 class="section-title">
                    <div class="section-icon">🎛️</div>
                    Management Tools
                </h2>
                
                <div class="dashboard-actions">
                    <div class="action-card">
                        <div class="action-icon">⚙️</div>
                        <h3>Manage Services</h3>
                        <p>Configure service cards, descriptions, and links displayed on the homepage</p>
                        <a href="manage-services.php" class="admin-btn">Manage Services</a>
                    </div>
                    
                    <div class="action-card">
                        <div class="action-icon">📢</div>
                        <h3>Manage Updates</h3>
                        <p>Add, edit, and manage community announcements and latest news updates</p>
                        <a href="manage-updates.php" class="admin-btn">Manage Updates</a>
                    </div>
                    
                    <div class="action-card">
                        <div class="action-icon">👥</div>
                        <h3>Census Registrations</h3>
                        <p>View and manage resident census registrations</p>
                        <a href="view-resident-registrations.php" class="admin-btn">View Submissions</a>
                    </div>
                    
                    <div class="action-card">
                        <div class="action-icon">📄</div>
                        <h3>Certificate Requests</h3>
                        <p>Process certificate requests and approvals</p>
                        <a href="view-certificate-requests.php" class="admin-btn">View Requests</a>
                    </div>
                    
                    <div class="action-card">
                        <div class="action-icon">🏢</div>
                        <h3>Business Applications</h3>
                        <p>Review business permit applications</p>
                        <a href="view-business-applications.php" class="admin-btn">View Applications</a>
                    </div>
                    
                    <div class="action-card">
                        <div class="action-icon">📝</div>
                        <h3>Blotter Management</h3>
                        <p>Record and manage complaints, incidents, and disputes within the barangay</p>
                        <a href="manage-blotter.php" class="admin-btn">Manage Blotter</a>
                    </div>
                    
                    <div class="action-card">
                        <div class="action-icon">🛡️</div>
                        <h3>Captain Clearances</h3>
                        <p>Manage clearances for residents with records or requiring special permissions</p>
                        <a href="captain-clearances.php" class="admin-btn">Manage Clearances</a>
                    </div>
                    
                    <div class="action-card">
                        <div class="action-icon">📈</div>
                        <h3>Blotter Reports</h3>
                        <p>Generate reports and analyze trends in community incidents and resolutions</p>
                        <a href="blotter-reports.php" class="admin-btn">View Reports</a>
                    </div>
                    
                    <div class="action-card">
                        <div class="action-icon">📊</div>
                        <h3>Forms Manager</h3>
                        <p>Comprehensive form management and analytics</p>
                        <a href="forms-manager.php" class="admin-btn">Open Manager</a>
                    </div>
                    
                    <div class="action-card">
                        <div class="action-icon">📱</div>
                        <h3>RFID Scanner</h3>
                        <p>Scan and manage RFID codes for resident registration</p>
                        <a href="rfid-scanner.php" class="admin-btn">Open Scanner</a>
                    </div>
                    
                    <div class="action-card">
                        <div class="action-icon">🎫</div>
                        <h3>Queue Management</h3>
                        <p>Monitor and control the queue system, manage service counters and tickets</p>
                        <a href="queue-admin.php" class="admin-btn">Manage Queue</a>
                    </div>
                    
                    <div class="action-card">
                        <div class="action-icon">📋</div>
                        <h3>System Logs</h3>
                        <p>View activity logs and system events</p>
                        <a href="view-logs.php" class="admin-btn">View Logs</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="dashboard-section queue-section">
            <h2 class="section-title">
                <div class="section-icon">🖥️</div>
                Queue Monitor
            </h2>
            <div class="queue-stats" id="queue-stats">
                <div class="qstat"><div class="qnum">0</div><div class="qlabel">Waiting</div></div>
                <div class="qstat"><div class="qnum">0</div><div class="qlabel">Serving</div></div>
                <div class="qstat"><div class="qnum">0</div><div class="qlabel">Completed</div></div>
                <div class="qstat"><div class="qnum">0</div><div class="qlabel">Cancelled</div></div>
                <div class="qstat"><div class="qnum">0</div><div class="qlabel">Total</div></div>
            </div>
            <div class="queue-lists">
                <div class="queue-panel">
                    <h3>Currently Serving</h3>
                    <div class="queue-list" id="queue-serving"></div>
                </div>
                <div class="queue-panel">
                    <h3>Waiting Queue</h3>
                    <div class="queue-list" id="queue-waiting"></div>
                </div>
            </div>
            <div class="queue-actions">
                <a href="queue-monitor.php" class="queue-link">Open Full Monitor</a>
            </div>
        </div>
    </div>
    
    <script>
        // Add smooth scrolling to dashboard
        document.addEventListener('DOMContentLoaded', function() {
            // Animate numbers counting up
            const numbers = document.querySelectorAll('.stat-number');
            numbers.forEach(number => {
                const finalNumber = parseInt(number.textContent);
                let currentNumber = 0;
                const increment = finalNumber / 50;
                
                const timer = setInterval(() => {
                    currentNumber += increment;
                    if (currentNumber >= finalNumber) {
                        currentNumber = finalNumber;
                        clearInterval(timer);
                    }
                    number.textContent = Math.floor(currentNumber);
                }, 20);
            });
            
            // Add hover effects to stat cards
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-8px) scale(1.02)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
            });
        });
        
        // Queue Monitor (Dashboard preview)
        (function() {
            const apiUrl = '../api/queue-state.php';
            const statsEl = document.getElementById('queue-stats');
            const servingEl = document.getElementById('queue-serving');
            const waitingEl = document.getElementById('queue-waiting');
            if (!statsEl || !servingEl || !waitingEl) return;

            function escapeHtml(s) {
                if (s == null) return '';
                return String(s).replace(/[&<>\"]+/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c] || c));
            }

            function badgeForPriority(level) {
                if (level === 'urgent') return '<span class="qbadge urgent">Urgent</span>';
                if (level === 'priority') return '<span class="qbadge priority">Priority</span>';
                return '';
            }

            function renderQueue() {
                fetch(apiUrl, { credentials: 'same-origin' })
                    .then(r => r.json())
                    .then(data => {
                        const s = data && data.stats ? data.stats : {};
                        statsEl.innerHTML = `
                            <div class=\"qstat\"><div class=\"qnum\">${s.waiting || 0}</div><div class=\"qlabel\">Waiting</div></div>
                            <div class=\"qstat\"><div class=\"qnum\">${s.serving || 0}</div><div class=\"qlabel\">Serving</div></div>
                            <div class=\"qstat\"><div class=\"qnum\">${s.completed || 0}</div><div class=\"qlabel\">Completed</div></div>
                            <div class=\"qstat\"><div class=\"qnum\">${s.cancelled || 0}</div><div class=\"qlabel\">Cancelled</div></div>
                            <div class=\"qstat\"><div class=\"qnum\">${s.total || 0}</div><div class=\"qlabel\">Total</div></div>
                        `;

                        const serving = (data && data.serving) ? data.serving : [];
                        servingEl.innerHTML = serving.map(t => `
                            <div class=\"queue-ticket\">
                                <div>
                                    <div><strong>${escapeHtml(t.ticket_number)}</strong> — ${escapeHtml(t.service_name || '')}</div>
                                    <div style=\"font-size:12px;color:#555\">${escapeHtml(t.customer_name || '')}</div>
                                </div>
                                <div class=\"qbadges\">
                                    <span class=\"qbadge serving\">${escapeHtml(t.window_number || t.window_name || 'Serving')}</span>
                                </div>
                            </div>
                        `).join('');

                        const waiting = (data && data.waiting) ? data.waiting : [];
                        waitingEl.innerHTML = waiting.slice(0, 15).map(t => `
                            <div class=\"queue-ticket\">
                                <div>
                                    <div><strong>${escapeHtml(t.ticket_number)}</strong> — ${escapeHtml(t.service_name || '')}</div>
                                    <div style=\"font-size:12px;color:#555\">#${t.queue_position ?? '-'} · ${escapeHtml(t.priority_level || 'normal')}</div>
                                </div>
                                <div class=\"qbadges\">
                                    <span class=\"qbadge waiting\">Waiting</span>
                                    ${badgeForPriority(t.priority_level)}
                                </div>
                            </div>
                        `).join('');
                    })
                    .catch(() => {
                        // Silent fail on preview
                    });
            }

            renderQueue();
            setInterval(renderQueue, 10000);
        })();
    </script>
</body>
</html>
