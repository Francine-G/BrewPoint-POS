<?php
    // Database connection - adjust path to your database config
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "brewpos";

    try {
        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) {
            die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]));
        }
    } catch (Exception $e) {
        die(json_encode(['success' => false, 'message' => 'Database connection error: ' . $e->getMessage()]));
    }

    // Check if order_id is provided
    if (!isset($_POST['order_id']) || empty($_POST['order_id'])) {
        echo json_encode(['success' => false, 'message' => 'Order ID is required']);
        exit;
    }

    $orderId = $_POST['order_id'];

    // Validate order exists and is in progress
    $checkSql = "SELECT status FROM orders WHERE order_id = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("s", $orderId);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit;
    }

    $order = $result->fetch_assoc();
    if ($order['status'] !== 'in_progress') {
        echo json_encode(['success' => false, 'message' => 'Order is not in progress']);
        exit;
    }

    // Update order status to completed
    $updateSql = "UPDATE orders SET status = 'completed' WHERE order_id = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("s", $orderId);

    if ($updateStmt->execute()) {
        if ($updateStmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Order completed successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No changes made']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating order: ' . $conn->error]);
    }

    $conn->close();
?>