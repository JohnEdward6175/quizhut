<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ensure the user is logged in and is a teacher
if (!isset($_SESSION['user']) || $_SESSION['user']['user_type'] !== 'teacher') {
    header("Location: login.php");
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Quiz Management - QuizHut</title>
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
      position: relative;
    }
    .welcome-banner i {
      color: white;
      font-size: 3rem;
      margin-bottom: 0.5rem;
    }
    .back-icon {
      position: absolute;
      top: 1rem;
      left: 1rem;
      color: white;
      font-size: 1.5rem;
      text-decoration: none;
    }
    .back-icon:hover { color: #e0d4f7; }
    .card {
      border-radius: 0.75rem;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .card i {
      font-size: 2rem;
      margin-bottom: 0.5rem;
    }
    .card-title {
      margin-bottom: 0.5rem;
      font-weight: bold;
    }
    .card-text {
      font-size: 0.9rem;
    }
    .navbar .navbar-brand, .navbar a, .navbar span {
      color: white !important;
    }
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
    <a class="navbar-brand fw-bold" href="teacher_dashboard.php">QuizHut</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item">
          <a class="nav-link" href="lessons_upload.php">Lessons</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="quiz_upload.php">Quiz</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="reviewer_upload.php">Reviewer</a>
        </li>
      </ul>
      <a class="btn btn-outline-light btn-sm" href="home.php">Logout</a>
    </div>
  </div>
</nav>

<!-- Welcome Banner -->
<div class="container">
  <div class="welcome-banner">
    <a href="teacher_dashboard.php" class="back-icon" title="Back to Dashboard"><i class="bi bi-arrow-left-circle"></i></a>
    <i class="bi bi-card-checklist"></i>
    <h1 class="display-6 fw-bold">Quiz Management</h1>
    <p class="lead mb-0">Create, edit, and manage quizzes for your students.</p>
  </div>
</div>

<!-- Main content -->
<div class="container">
  <div class="row justify-content-center g-3">
    <div class="col-12 col-md-4">
      <div class="card p-4 text-center">
        <i class="bi bi-plus-circle text-white mb-2"></i>
        <h5 class="card-title">Create New Quiz</h5>
        <p class="card-text">Build a new quiz with questions and answers.</p>
        <a href="quiz_create.php" class="btn btn-purple w-100">Create Quiz</a>
      </div>
    </div>
    <div class="col-12 col-md-4">
      <div class="card p-4 text-center">
        <i class="bi bi-pencil-square text-white mb-2"></i>
        <h5 class="card-title">Edit Existing Quizzes</h5>
        <p class="card-text">Modify questions, answers, or settings in your quizzes.</p>
        <a href="quiz_edit.php" class="btn btn-purple w-100">Edit Quizzes</a>
      </div>
    </div>
    <div class="col-12 col-md-4">
      <div class="card p-4 text-center">
        <i class="bi bi-bar-chart-line text-white mb-2"></i>
        <h5 class="card-title">View Quiz Results</h5>
        <p class="card-text">Review student performance and quiz statistics.</p>
        <a href="quiz_history.php" class="btn btn-purple w-100">View Results</a>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
