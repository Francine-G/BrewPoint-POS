<?php
// Include database connection
include("../database/db.php");

// Initialize variables
$productName = $productCategory = $productImg = "";
$errors = [];

// Validate form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate product name
    if (empty($_POST['productName'])) {
        $errors[] = "Product name is required";
    } else {
        $productName = trim($_POST['productName']);
        // Additional validation if needed
    }
    
    // Validate product category
    if (empty($_POST['productCategory'])) {
        $errors[] = "Product category is required";
    } else {
        $productCategory = trim($_POST['productCategory']);
    }
    
    // Handle image upload
    $productImg = "";
    if (isset($_FILES['productImg']) && $_FILES['productImg']['error'] === UPLOAD_ERR_OK) {
        $imgTmpName = $_FILES['productImg']['tmp_name'];
        $originalFileName = $_FILES['productImg']['name'];
        
        // Get file extension
        $fileExtension = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));
        
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (!in_array($_FILES['productImg']['type'], $allowedTypes) || !in_array($fileExtension, $allowedExtensions)) {
            $errors[] = "Only JPG, PNG, and GIF files are allowed.";
        }
        
        // Create filename using product name
        if (empty($errors)) {
            // Clean product name for filename (remove special characters, spaces, etc.)
            $cleanProductName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $productName);
            $cleanProductName = preg_replace('/_+/', '_', $cleanProductName); // Replace multiple underscores with single
            $cleanProductName = trim($cleanProductName, '_'); // Remove leading/trailing underscores
            
            // Create new filename: productname_image.extension
            $imgName = $cleanProductName . '_image.' . $fileExtension;
            
            // Define upload path
            $uploadDir = "../assets/img/uploads/";
            
            // Check if directory exists, create if not
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $imgPath = $uploadDir . $imgName;
            
            // Check if file already exists and create unique name if needed
            $counter = 1;
            $originalImgName = $imgName;
            while (file_exists($imgPath)) {
                $imgName = $cleanProductName . '_image_' . $counter . '.' . $fileExtension;
                $imgPath = $uploadDir . $imgName;
                $counter++;
            }
            
            // Upload file
            if (move_uploaded_file($imgTmpName, $imgPath)) {
                $productImg = $imgName;
            } else {
                $errors[] = "Failed to upload image.";
            }
        }
    }
    
    // If no errors, insert into database
    if (empty($errors)) {
        // Check if product name already exists
        $checkStmt = $conn->prepare("SELECT productID FROM products WHERE productName = ?");
        $checkStmt->bind_param("s", $productName);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            $errors[] = "A product with this name already exists.";
            $checkStmt->close();
        } else {
            $checkStmt->close();
            
            // Use prepared statement to prevent SQL injection
            $stmt = $conn->prepare("INSERT INTO products (productName, productCategory, productImg) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $productName, $productCategory, $productImg);
            
            if ($stmt->execute()) {
                $stmt->close();
                // Redirect on success
                header("Location: ../product-modification.php?msg=added");
                exit();
            } else {
                $errors[] = "Database error: " . $conn->error;
            }
            $stmt->close();
        }
    }
}

// If we get here, there were errors
if (!empty($errors)) {
    // If there was an uploaded file but errors occurred, clean up the file
    if (!empty($productImg) && file_exists("../assets/img/uploads/" . $productImg)) {
        unlink("../assets/img/uploads/" . $productImg);
    }
    
    echo "<div class='error-messages'>";
    foreach ($errors as $error) {
        echo "<p>Error: $error</p>";
    }
    echo "</div>";
    echo "<p><a href='javascript:history.back()'>Go back</a></p>";
}
?>