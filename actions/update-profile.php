<?php
session_start();
include("../database/db.php"); // Fixed path - should go up two levels from actions folder

if (!isset($_SESSION['userID'])) {
    header("Location: ../index.php"); // Fixed path
    exit();
}

$userID = $_SESSION['userID'];

// Initialize variables
$profile_picture_updated = false;
$uploadOk = 1;
$target_dir = "../assets/img/profiles/"; // Fixed path - go up one level from actions folder

// Create profiles directory if it doesn't exist
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0777, true);
}

// Handle file upload
if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
    $fileExt = strtolower(pathinfo(basename($_FILES["profile_picture"]["name"]), PATHINFO_EXTENSION));
    $allowedExts = ['jpg', 'jpeg', 'png', 'gif'];
    $maxFileSize = 5 * 1024 * 1024; // 5MB
    
    if (in_array($fileExt, $allowedExts)) {
        if ($_FILES['profile_picture']['size'] <= $maxFileSize) {
            $new_filename = uniqid('profile_', true) . '.' . $fileExt;
            $target_file = $target_dir . $new_filename;
            
            if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
                // Update database with new filename
                $pic_stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE userID = ?");
                $pic_stmt->bind_param("si", $new_filename, $userID);
                $pic_stmt->execute();
                
                // Set session variable for profile picture
                $_SESSION['profile_picture'] = $new_filename;
                $profile_picture_updated = true;
            } else {
                $uploadOk = 0;
                $_SESSION['error'] = "Failed to upload file. Please try again.";
            }
        } else {
            $uploadOk = 0;
            $_SESSION['error'] = "File size too large. Maximum size allowed is 5MB.";
        }
    } else {
        $uploadOk = 0;
        $_SESSION['error'] = "Invalid file type. Only JPG, JPEG, PNG & GIF files are allowed.";
    }
} elseif (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] !== UPLOAD_ERR_NO_FILE) {
    $uploadOk = 0;
    $_SESSION['error'] = "File upload error occurred.";
}

// FIXED: Changed from $_POST['uname'] to $_POST['username'] to match form field name
if (empty($_POST['username'])) {
    $_SESSION['error'] = "Username is required.";
    header("Location: ../user-settings.php"); // Fixed path
    exit();
}

// Check if username is taken by another user
$username_check = "SELECT userID FROM users WHERE uname = ? AND userID != ?";
$username_stmt = $conn->prepare($username_check);
$username_stmt->bind_param("si", $_POST['username'], $userID);
$username_stmt->execute();
$username_result = $username_stmt->get_result();

if ($username_result->num_rows > 0) {
    $_SESSION['error'] = "Username is already taken. Please choose a different one.";
    header("Location: ../user-settings.php"); // Fixed path
    exit();
}

// Prepare update query
$sql = "UPDATE users SET uname=?";
$params = [$_POST['username']];
$types = "s";

// Add password if provided
if (!empty($_POST['password'])) {
    if (strlen($_POST['password']) < 6) {
        $_SESSION['error'] = "Password must be at least 6 characters long.";
        header("Location: ../user-settings.php"); // Fixed path
        exit();
    }
    
    $sql .= ", pw=?";
    $params[] = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $types .= "s";
}

$sql .= " WHERE userID=?";
$params[] = $userID;
$types .= "i";

// Execute the update query
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    // Update session variables
    $_SESSION['uname'] = $_POST['username'];
    
    $success_message = "Profile updated successfully";
    if ($profile_picture_updated) {
        $success_message .= " including your profile picture";
    }
    $success_message .= ".";
    
    $_SESSION['success'] = $success_message;
} else {
    $_SESSION['error'] = "Error updating profile. Please try again.";
}

// If there was an upload error but other updates succeeded
if ($uploadOk === 0 && !isset($_SESSION['error'])) {
    $_SESSION['error'] = "Profile updated, but there was an issue with the profile picture upload.";
}

header("Location: ../user-settings.php"); // Fixed path
exit();
?>