<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>QuizHut - Home</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background:linear-gradient(135deg,#eff6ff,#e9d5ff);min-height:100vh;">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
  <div class="container">
    <a class="navbar-brand text-primary fw-bold" href="home.php">QuizHut</a>
    <div>
      <?php
      // ✅ Show "Hello" only if logged in and not a guest
      if (isset($_SESSION['user']) && $_SESSION['user']['user_type'] !== 'guest') {
          echo '<span class="me-2">Hello, ' . htmlspecialchars($_SESSION['user']['username']) . '</span>';
          echo '<a class="btn btn-outline-secondary btn-sm" href="logout.php">Logout</a>';
      } else {
          // For guests and visitors
          echo '<a class="btn btn-link" href="guest.php">Guest</a>';
          echo '<a class="btn btn-link ms-2" href="login.php">Login</a>';
          echo '<a class="btn btn-primary ms-2" href="register.php">Sign Up</a>';
      }
      ?> 
    </div>
  </div>
</nav>

<main class="container py-5">
  <div class="text-center py-5">
    <h1 class="display-5 fw-bold">Learn Something New <span class="text-primary">Today</span></h1>
    <p class="lead text-muted">Access interactive lessons and test your knowledge with engaging quizzes.</p>
    <div class="d-flex justify-content-center gap-3">
      <a class="btn btn-primary btn-lg" href="quiz.php">Take Sample Quiz</a>
      <!-- <a class="btn btn-outline-primary btn-lg" href="setup_db.php">Setup DB</a> -->
    </div>
  </div>
</main>

</body>
</html>
