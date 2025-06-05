<?php
// File: test-supplier-export.php
// Create this file in your root directory to test the connection

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><title>Test Supplier Export</title></head><body>";
echo "<h2>Testing Supplier Export System</h2>";

// Test 1: Check if the actions directory exists
echo "<h3>1. Directory Check:</h3>";
if (is_dir('actions')) {
    echo "✅ actions/ directory exists<br>";
} else {
    echo "❌ actions/ directory NOT found<br>";
}

// Test 2: Check if the PHP file exists
echo "<h3>2. File Check:</h3>";
if (file_exists('actions/get-filtered-suppliers.php')) {
    echo "✅ get-filtered-supplier.php exists<br>";
} else {
    echo "❌ get-filtered-supplier.php NOT found<br>";
}

// Test 3: Test database connection
echo "<h3>3. Database Connection Test:</h3>";
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "brewpos";

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        throw new Exception('Connection failed: ' . $conn->connect_error);
    }
    echo "✅ Database connection successful<br>";
    
    // Test 4: Check if suppliers table exists
    echo "<h3>4. Table Check:</h3>";
    $result = $conn->query("SHOW TABLES LIKE 'suppliers'");
    if ($result->num_rows > 0) {
        echo "✅ suppliers table exists<br>";
        
        // Test 5: Count suppliers
        echo "<h3>5. Data Check:</h3>";
        $countResult = $conn->query("SELECT COUNT(*) as total FROM suppliers");
        $count = $countResult->fetch_assoc()['total'];
        echo "✅ Found {$count} suppliers in database<br>";
        
        // Test 6: Show sample data
        if ($count > 0) {
            echo "<h3>6. Sample Data:</h3>";
            $sampleResult = $conn->query("SELECT supplierID, supplierName, supplierProduct FROM suppliers LIMIT 3");
            echo "<ul>";
            while ($row = $sampleResult->fetch_assoc()) {
                echo "<li>ID: {$row['supplierID']} - {$row['supplierName']} ({$row['supplierProduct']})</li>";
            }
            echo "</ul>";
        }
    } else {
        echo "❌ suppliers table NOT found<br>";
    }
    
    $conn->close();
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
}

// Test 7: Test the AJAX endpoint directly
echo "<h3>7. AJAX Endpoint Test:</h3>";
echo "<button onclick='testAjax()'>Test AJAX Connection</button>";
echo "<div id='ajaxResult'></div>";

?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function testAjax() {
    console.log('Testing AJAX connection...');
    $('#ajaxResult').html('Testing...');
    
    $.ajax({
        url: 'actions/get-filtered-supplier.php',
        type: 'POST',
        data: { filter: 'all' },
        success: function(response) {
            console.log('AJAX Response:', response);
            $('#ajaxResult').html('<div style="color: green;">✅ AJAX Success: ' + JSON.stringify(response).substring(0, 200) + '...</div>');
        },
        error: function(xhr, status, error) {
            console.log('AJAX Error:', xhr.responseText);
            $('#ajaxResult').html('<div style="color: red;">❌ AJAX Error: ' + status + ' - ' + error + '<br>Response: ' + xhr.responseText + '</div>');
        }
    });
}
</script>

</body></html>