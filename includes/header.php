<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include functions file
require_once __DIR__ . '/functions.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Rental System</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/Car-Rental/assets/css/style.css">
    <?php if (isset($isAdminPage) && $isAdminPage): ?>
    <link rel="stylesheet" href="/Car-Rental/assets/css/dashboard.css">
    <?php endif; ?>
</head>

<body>
    <?php include_once __DIR__ . '/navbar.php'; ?>
    <div class="<?php echo isset($isAdminPage) && $isAdminPage ? 'container-fluid' : 'container'; ?> mt-4">
        <?php if (isset($isAdminPage) && $isAdminPage): ?>
        <div class="row">
            <div class="col-md-2">
                <?php include_once __DIR__ . '/sidebar.php'; ?>
            </div>
            <div class="col-md-10">
                <?php endif; ?>