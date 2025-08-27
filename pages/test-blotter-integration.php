<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Blotter Detection Integration</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: #f8f9fa;
        }
        .container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        .form-group {
            margin-bottom: 1rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        input {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }
        button {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            margin: 0.5rem;
        }
        button:hover {
            transform: translateY(-2px);
        }
        .result {
            margin-top: 2rem;
            padding: 1rem;
            border-radius: 5px;
            display: none;
        }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
        .info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🛡️ Blotter Detection System Test</h1>
        <p>This page tests the blotter detection integration that was added to the resident registration form.</p>
        
        <div class="form-group">
            <label for="firstName">First Name:</label>
            <input type="text" id="firstName" value="Mar Yvan">
        </div>
        
        <div class="form-group">
            <label for="middleName">Middle Name:</label>
            <input type="text" id="middleName" value="Sagun">
        </div>
        
        <div class="form-group">
            <label for="lastName">Last Name:</label>
            <input type="text" id="lastName" value="Dela Cruz">
        </div>
        
        <button onclick="testBlotterDetection()">🔍 Test Blotter Detection</button>
        <button onclick="clearForm()">🗑️ Clear Form</button>
        <button onclick="window.location.href='resident-registration.php'">📋 Go to Registration Form</button>
        
        <div id="result" class="result"></div>
    </div>

    <script>
        async function testBlotterDetection() {
            const firstName = document.getElementById('firstName').value.trim();
            const middleName = document.getElementById('middleName').value.trim();
            const lastName = document.getElementById('lastName').value.trim();
            const resultDiv = document.getElementById('result');
            
            if (!firstName || !lastName) {
                showResult('Please enter at least first name and last name', 'error');
                return;
            }
            
            try {
                showResult('🔄 Checking blotter records...', 'info');
                
                const response = await fetch('check-blotter.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `action=check_blotter&first_name=${encodeURIComponent(firstName)}&middle_name=${encodeURIComponent(middleName)}&last_name=${encodeURIComponent(lastName)}`
                });
                
                const result = await response.json();
                
                if (result.has_unresolved_issues) {
                    showResult(`⚠️ UNRESOLVED ISSUES FOUND!\n\nMessage: ${result.message}\n\nTotal Cases: ${result.details?.total_cases || 0}\n\nThis would trigger the warning modal in the registration form.`, 'warning');
                } else {
                    showResult(`✅ No unresolved issues found.\n\nMessage: ${result.message}\n\nThe registration form would proceed normally.`, 'success');
                }
                
                console.log('Full blotter check result:', result);
                
            } catch (error) {
                console.error('Error testing blotter detection:', error);
                showResult(`❌ Error testing blotter detection: ${error.message}`, 'error');
            }
        }
        
        function showResult(message, type) {
            const resultDiv = document.getElementById('result');
            resultDiv.textContent = message;
            resultDiv.className = `result ${type}`;
            resultDiv.style.display = 'block';
        }
        
        function clearForm() {
            document.getElementById('firstName').value = '';
            document.getElementById('middleName').value = '';
            document.getElementById('lastName').value = '';
            document.getElementById('result').style.display = 'none';
        }
    </script>
</body>
</html>