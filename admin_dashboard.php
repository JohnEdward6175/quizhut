<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: home.php');
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Admin Dashboard - QuizHut</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    .navbar, .btn-success { background-color: #198754 !important; }
    .welcome-card { background-color: #198754; color: white; border-radius: 0.5rem; }
    .welcome-card i { color: white; }
    .dashboard-card { border-radius: 0.5rem; height: 100%; }
  </style>
</head>
<body class="bg-light">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold" href="admin_dashboard.php">QuizHut</a>
    <div>
      <span class="me-2" style="color:white;">Hello, AdminUser</span>
      <a class="btn btn-outline-light btn-sm" href="admin_dashboard.php?logout=true">Logout</a>
    </div>
  </div>
</nav>

<!-- Main content -->
<div class="container py-5">
  <div class="row justify-content-center mb-4">
    <div class="col-12">
      <div class="card p-4 text-center welcome-card">
        <i class="bi bi-shield-check fs-1 mb-2"></i>
        <h1 class="mb-3">Welcome, Admin!</h1>
        <p class="lead">Manage accounts, review quizzes, and handle study materials efficiently.</p>
      </div>
    </div>
  </div>

  <div class="row justify-content-center">
    <div class="col-12 col-md-5 mb-3">
      <div class="card p-4 text-center dashboard-card shadow-sm">
        <i class="bi bi-person-lines-fill fs-1 text-success mb-2"></i>
        <h5 class="card-title">Account Handling</h5>
        <p class="card-text">Create, update, or remove user accounts and manage roles.</p>
        <a href="account_management.php" class="btn btn-success">Manage Accounts</a>
      </div>
    </div>
    <div class="col-12 col-md-5 mb-3">
      <div class="card p-4 text-center dashboard-card shadow-sm">
        <i class="bi bi-journal-bookmark fs-1 text-success mb-2"></i>
        <h5 class="card-title">Study Materials Management</h5>
        <p class="card-text">Upload, edit, and organize lessons, quizzes, and reviewers.</p>
        <a href="study_materials.php" class="btn btn-success">Manage Materials</a>
      </div>
    </div>
  </div>
</div>

</body>
</html>
