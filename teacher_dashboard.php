<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Allow teachers or guests
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['user_type'], ['teacher', 'guest'])) {
    header("Location: login.php");
    exit;
}

// Determine user info
if ($_SESSION['user']['user_type'] === 'guest') {
    $username = $_SESSION['user']['username'] ?? 'Guest Teacher';
} else {
    $username = $_SESSION['user']['username'] ?? 'Teacher';
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Teacher Dashboard - QuizHut</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    .navbar, .btn-primary {
      background-color: #6f42c1 !important; /* Purple */
      border-color: #6f42c1 !important;
    }
    .btn-primary:hover {
      background-color: #5a32a3 !important;
      border-color: #5a32a3 !important;
    }
    .welcome-banner {
      background-color: #6f42c1;
      color: white;
      border-radius: 0.5rem;
      padding: 2rem 1rem;
      text-align: center;
      margin-bottom: 2rem;
    }
    .welcome-banner i { color: white; }
    .card {
      border-radius: 0.75rem;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      color: white;
      background-color: #6f42c1;
    }
    .card i {
      font-size: 2rem;
      margin-bottom: 0.5rem;
      color: white;
    }
    .card-title { margin-bottom: 0.5rem; font-weight: bold; }
    .card-text { font-size: 0.9rem; }
    .btn-purple {
      background-color: white;
      color: #6f42c1;
      font-weight: bold;
      border: 1px solid #6f42c1;
    }
    .btn-purple:hover {
      background-color: #e0d4f7;
      color: #6f42c1;
    }
  </style>
</head>
<body class="bg-light">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold" href="home.php">QuizHut</a>
    <div>
      <span class="me-2 text-white">Hello, <?php echo htmlspecialchars($username); ?></span>
      <a class="btn btn-outline-light btn-sm" href="logout.php">Logout</a>
    </div>
  </div>
</nav>

<!-- Welcome Banner -->
<div class="container">
  <div class="welcome-banner">
    <i class="bi bi-person-badge fs-1 mb-2"></i>
    <h1 class="display-6 fw-bold">Welcome, <?php echo htmlspecialchars($username); ?>!</h1>
    <p class="lead mb-0">Manage lessons, quizzes, and reviewers efficiently from your dashboard.</p>
  </div>
</div>

<!-- Main content -->
<div class="container">
  <div class="row justify-content-center g-3">
    <!-- Lessons Upload -->
    <div class="col-12 col-md-4">
      <div class="card p-4 text-center">
        <i class="bi bi-journal-plus mb-2"></i>
        <h5 class="card-title">Lesson Creation & Upload</h5>
        <p class="card-text">Create new lessons and upload them for students.</p>
        <a href="lessons_upload.php" class="btn btn-purple w-100">Go to Lessons</a>
      </div>
    </div>
    <!-- Quiz Upload -->
    <div class="col-12 col-md-4">
      <div class="card p-4 text-center">
        <i class="bi bi-card-checklist mb-2"></i>
        <h5 class="card-title">Quiz Management</h5>
        <p class="card-text">Create, edit, and manage quizzes for your students.</p>
        <a href="quiz_upload.php" class="btn btn-purple w-100">Manage Quizzes</a>
      </div>
    </div>
    <!-- Reviewer Upload -->
    <div class="col-12 col-md-4">
      <div class="card p-4 text-center">
        <i class="bi bi-journal-text mb-2"></i>
        <h5 class="card-title">Reviewer</h5>
        <p class="card-text">Create and publish reviewers for students to practice.</p>
        <a href="reviewer_upload.php" class="btn btn-purple w-100">Go to Reviewer</a>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
