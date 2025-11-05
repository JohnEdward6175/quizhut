<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Allow students or guests
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['user_type'], ['student', 'guest'])) {
    header("Location: login.php");
    exit;
}

// Determine user info
if ($_SESSION['user']['user_type'] === 'guest') {
    $username = $_SESSION['user']['username'] ?? 'Guest';
} else {
    $username = $_SESSION['user']['username'] ?? 'Student';
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Student Dashboard - QuizHut</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    .navbar, .btn-primary {
      background-color: #0d6efd !important;
    }
    .navbar .navbar-brand, .navbar a, .navbar span {
      color: white !important;
    }
    .card-dashboard {
      border-radius: 0.5rem;
      min-height: 260px;
    }
    .welcome-banner {
      border-radius: 0.5rem;
      background-color: #0d6efd;
      color: white;
      padding: 2rem;
      display: flex;
      align-items: center;
      gap: 1.5rem;
      margin-bottom: 2rem;
    }
    .welcome-banner i {
      font-size: 3rem;
      color: white;
    }
    .btn-primary {
      color: white !important;
    }
  </style>
</head>
<body class="bg-light">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold" href="home.php">QuizHut</a>
    <div>
      <span class="me-2 text-white">Hello, <?php echo htmlspecialchars($username); ?></span>
      <a class="btn btn-outline-light btn-sm" href="logout.php">Logout</a>
    </div>
  </div>
</nav>

<!-- Main content -->
<div class="container py-5">
  <!-- Welcome Banner -->
  <div class="welcome-banner">
    <i class="bi bi-house-door"></i>
    <div>
      <h1 class="fw-bold mb-1">Welcome, <?php echo htmlspecialchars($username); ?>!</h1>
      <p class="mb-0">This is your dashboard. You can take quizzes, view results, and more.</p>
    </div>
  </div>

  <!-- Dashboard Cards -->
  <div class="row g-3">
    <div class="col-12 col-md-4">
      <div class="card p-4 card-dashboard text-center shadow-sm">
        <i class="bi bi-book fs-1 text-primary mb-2"></i>
        <h5 class="card-title">Access Lessons</h5>
        <p class="card-text">Browse and access available lessons.</p>
        <a href="lessons.php" class="btn btn-primary">Go to Lessons</a>
      </div>
    </div>

    <div class="col-12 col-md-4">
      <div class="card p-4 card-dashboard text-center shadow-sm">
        <i class="bi bi-clipboard-check fs-1 text-primary mb-2"></i>
        <h5 class="card-title">Take a Quiz</h5>
        <p class="card-text">Start taking quizzes to test your knowledge.</p>
        <a href="quiz.php" class="btn btn-primary">Take a Quiz</a>
      </div>
    </div>

    <div class="col-12 col-md-4">
      <div class="card p-4 card-dashboard text-center shadow-sm">
        <i class="bi bi-pencil-square fs-1 text-primary mb-2"></i>
        <h5 class="card-title">Reviewers</h5>
        <p class="card-text">View and use available reviewers.</p>
        <a href="reviewer.php" class="btn btn-primary">Open Reviewer</a>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
