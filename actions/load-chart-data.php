<?php
// actions/load-chart-data.php
include("../database/db.php");

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $period = $_POST['period'] ?? 'daily';
    $chart_data = [];
    
    try {
        if ($period === 'daily') {
            // Query for daily sales (last 7 days)
            $chart_sql = "SELECT 
                DATE(o.order_date) as sale_date,
                COALESCE(SUM(o.total_amount), 0) as daily_total,
                COUNT(DISTINCT o.order_id) as order_count
                FROM orders o
                WHERE o.order_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                GROUP BY DATE(o.order_date)
                ORDER BY sale_date ASC";
                
        } else if ($period === 'weekly') {
            // Query for weekly sales (last 8 weeks)
            $chart_sql = "SELECT 
                YEARWEEK(o.order_date, 1) as week_year,
                WEEK(o.order_date, 1) as week_number,
                YEAR(o.order_date) as year,
                COALESCE(SUM(o.total_amount), 0) as weekly_total,
                COUNT(DISTINCT o.order_id) as order_count,
                MIN(DATE(o.order_date)) as week_start,
                MAX(DATE(o.order_date)) as week_end
                FROM orders o
                WHERE o.order_date >= DATE_SUB(CURDATE(), INTERVAL 8 WEEK)
                GROUP BY YEARWEEK(o.order_date, 1)
                ORDER BY week_year ASC";
        }
        
        $result = $conn->query($chart_sql);
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                if ($period === 'weekly') {
                    // Format week data
                    $row['week_label'] = "Week " . $row['week_number'] . " (" . 
                                        date('M j', strtotime($row['week_start'])) . " - " . 
                                        date('M j', strtotime($row['week_end'])) . ")";
                }
                $chart_data[] = $row;
            }
            
            // If no data found, create empty data points
            if (empty($chart_data)) {
                if ($period === 'daily') {
                    // Create 7 days of empty data
                    for ($i = 6; $i >= 0; $i--) {
                        $date = date('Y-m-d', strtotime("-$i days"));
                        $chart_data[] = [
                            'sale_date' => $date,
                            'daily_total' => 0,
                            'order_count' => 0
                        ];
                    }
                } else if ($period === 'weekly') {
                    // Create 8 weeks of empty data
                    for ($i = 7; $i >= 0; $i--) {
                        $weekStart = date('Y-m-d', strtotime("-$i weeks"));
                        $weekNumber = date('W', strtotime($weekStart));
                        $chart_data[] = [
                            'week_number' => $weekNumber,
                            'weekly_total' => 0,
                            'order_count' => 0,
                            'week_label' => "Week $weekNumber"
                        ];
                    }
                }
            }
            
            echo json_encode([
                'success' => true,
                'data' => $chart_data,
                'period' => $period
            ]);
            
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'Database query failed: ' . $conn->error
            ]);
        }
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Error: ' . $e->getMessage()
        ]);
    }
    
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid request method'
    ]);
}

$conn->close();
?>