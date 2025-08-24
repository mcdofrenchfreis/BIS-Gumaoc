<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprehensive Census Data Generator</title>
    <style>
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            max-width: 1000px;
            margin: 2rem auto;
            padding: 2rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
        }
        .container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #2e7d32;
            text-align: center;
            margin-bottom: 2rem;
            font-size: 2rem;
        }
        .btn {
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
            border: none;
            padding: 1.2rem 2.5rem;
            border-radius: 15px;
            font-size: 1.2rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
        }
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(76, 175, 80, 0.4);
        }
        .result {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 2rem;
            margin-top: 2rem;
            border-left: 6px solid #4CAF50;
            white-space: pre-line;
            font-family: 'Courier New', monospace;
            max-height: 600px;
            overflow-y: auto;
        }
        .success {
            color: #155724;
            background: #d4edda;
            border-color: #28a745;
        }
        .error {
            color: #721c24;
            background: #f8d7da;
            border-color: #dc3545;
        }
        .info {
            background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
            border: 2px solid #2196f3;
            color: #0d47a1;
            padding: 1.5rem;
            border-radius: 15px;
            margin-bottom: 2rem;
        }
        .info h3 {
            margin-top: 0;
            color: #1565c0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1rem;
            margin: 1rem 0;
        }
        .feature {
            background: rgba(255, 255, 255, 0.7);
            padding: 1rem;
            border-radius: 10px;
            border-left: 4px solid #4CAF50;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin: 1rem 0;
        }
        .stat {
            background: rgba(76, 175, 80, 0.1);
            padding: 1rem;
            border-radius: 10px;
            text-align: center;
            border: 2px solid rgba(76, 175, 80, 0.3);
        }
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #2e7d32;
        }
        .stat-label {
            font-size: 0.9rem;
            color: #666;
            margin-top: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎯 Comprehensive Census Data Generator</h1>
        
        <div class="info">
            <h3>📊 Enhanced Census Data Generator with Complete Livelihood Profiles</h3>
            <p><strong>This advanced tool creates 10 comprehensive census registration profiles with complete livelihood data for testing Tab 3 visibility improvements:</strong></p>
            
            <div class="features">
                <div class="feature">
                    <strong>🏰 Complete Livelihood Sections:</strong><br>
                    All 12 sections (A-L) with realistic data matching form structure
                </div>
                <div class="feature">
                    <strong>🎯 Enhanced Tab 3 Testing:</strong><br>
                    Perfect for testing improved visibility and contrast in readonly mode
                </div>
                <div class="feature">
                    <strong>🏠 Housing & Land Data:</strong><br>
                    Complete ownership, energy sources, utilities information
                </div>
                <div class="feature">
                    <strong>💼 Business & Income:</strong><br>
                    Diverse commercial activities and transportation options
                </div>
                <div class="feature">
                    <strong>👨‍👩‍👧‍👦 Family Demographics:</strong><br>
                    Random emails, birth dates, and proper gender mapping (Lalaki/Babae)
                </div>
                <div class="feature">
                    <strong>🌍 Cultural Diversity:</strong><br>
                    Urban professionals, farmers, OFW, indigenous, and senior families
                </div>
            </div>
            
            <div class="stats">
                <div class="stat">
                    <div class="stat-number">10</div>
                    <div class="stat-label">Complete Livelihood Profiles</div>
                </div>
                <div class="stat">
                    <div class="stat-number">31</div>
                    <div class="stat-label">Family Members (w/ birth dates)</div>
                </div>
                <div class="stat">
                    <div class="stat-number">35</div>
                    <div class="stat-label">Database Fields Populated</div>
                </div>
                <div class="stat">
                    <div class="stat-number">12</div>
                    <div class="stat-label">Livelihood Sections (A-L)</div>
                </div>
            </div>
            
            <p><strong>🎯 Perfect for testing the enhanced Tab 3 (Livelihood) visibility improvements with stronger contrast and better readability in readonly mode!</strong></p>
        </div>
        
        <form method="POST">
            <button type="submit" name="generate" class="btn">
                🚀 Generate Comprehensive Test Data
            </button>
        </form>
        
        <?php if (isset($_POST['generate'])): ?>
            <div class="result <?php echo isset($success) && $success ? 'success' : 'error'; ?>">
                <?php
                // Capture output from the generator script
                ob_start();
                include 'comprehensive_dummy_generator.php';
                $output = ob_get_clean();
                echo $output;
                ?>
            </div>
        <?php endif; ?>
        
        <div style="margin-top: 2rem; text-align: center; color: #666;">
            <h3>📋 Enhanced Livelihood Profiles for Tab 3 Testing:</h3>
            <div style="text-align: left; max-width: 900px; margin: 0 auto;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 1rem;">
                    <div>
                        <strong>💻 Tech Professional Family</strong><br>
                        <small>Complete modern setup: LPG cooking, flush toilet, municipal water, air conditioning, multiple vehicles, online business</small>
                    </div>
                    <div>
                        <strong>👴 Senior Community Leader</strong><br>
                        <small>Traditional owned home: Basic appliances, tricycle transport, pension income, simple but complete livelihood</small>
                    </div>
                    <div>
                        <small>Rental housing: Gas cooking, basic toilet, artesian well, minimal appliances, small business</small>
                    </div>
                    <div>
                        <strong>🌾 Rural Farming Family</strong><br>
                        <small>Agricultural focus: Owned farmland, wood cooking, well water, solar panels, organic farming, carabao transport</small>
                    </div>
                    <div>
                        <strong>🏥 Healthcare Professionals</strong><br>
                        <small>High-end setup: Owned property, modern appliances, multiple vehicles, medical clinic business</small>
                    </div>
                    <div>
                        <strong>✈️ OFW Family</strong><br>
                        <small>International income: Remittances, real estate investment, modern amenities, money transfer business</small>
                    </div>
                    <div>
                        <strong>🏞️ Indigenous Family</strong><br>
                        <small>Cultural preservation: Ancestral domain, traditional house, wood cooking, spring water, herbal medicine</small>
                    </div>
                    <div>
                        <strong>🏙️ Urban Professional Couple</strong><br>
                        <small>City lifestyle: Owned condo, modern utilities, digital business, washing machine, car ownership</small>
                    </div>
                    <div>
                        <strong>👵👴 Elderly Couple</strong><br>
                        <small>Senior-friendly: Accessible housing, basic appliances, wheelchair access, government benefits</small>
                    </div>
                    <div>
                        <strong>🚀 Young Entrepreneurs</strong><br>
                        <small>Business-focused: Startup ventures, delivery vehicles, e-commerce, food delivery, tech services</small>
                    </div>
                </div>
            </div>
            <p style="margin-top: 2rem;"><strong>💡 Enhanced Features:</strong> All profiles include complete livelihood data covering housing ownership, energy sources, utilities, appliances, transportation, business activities, and contraceptive methods. Perfect for testing the improved Tab 3 visibility with stronger contrast and better readability!</p>
        </div>
    </div>
</body>
</html>