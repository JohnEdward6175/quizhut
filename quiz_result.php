<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ensure user is a student
if (!isset($_SESSION['user']) || $_SESSION['user']['user_type'] !== 'student') {
    header('Location: login.php');
    exit;
}

// Connect to database
try {
    $pdo = new PDO('mysql:host=localhost;dbname=quizhut_db', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Fetch past quiz attempts for this student
$stmt = $pdo->prepare("
    SELECT qa.id, qa.score, qa.date_taken, q.title, q.id AS quiz_id
    FROM quiz_attempts qa
    JOIN quizzes q ON qa.quiz_id = q.id
    WHERE qa.student_id = ?
    ORDER BY qa.date_taken DESC
");
$stmt->execute([$_SESSION['user']['id']]);
$attempts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Quiz History - QuizHut</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    .navbar, .btn-primary { background-color: #0d6efd !important; border-color: #0d6efd !important; }
    .btn-primary:hover { background-color: #0056b3 !important; border-color: #0056b3 !important; }
    .welcome-banner { background-color: #0d6efd; color: white; border-radius: 0.5rem; padding: 2rem 1rem; text-align: center; margin-bottom: 2rem; }
    .card { border-radius: 0.75rem; box-shadow: 0 4px 12px rgba(0,0,0,0.1); margin-bottom: 1rem; }
    .card-header { display: flex; justify-content: space-between; align-items: center; }
    .navbar .navbar-brand, .navbar a, .navbar span { color: white !important; }
  </style>
</head>
<body class="bg-light">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold" href="student_dashboard.php">QuizHut</a>
    <ul class="navbar-nav me-auto">
      <li class="nav-item"><a class="nav-link" href="lessons.php">Access Lessons</a></li>
      <li class="nav-item"><a class="nav-link" href="quiz.php">Take a Quiz</a></li>
      <li class="nav-item"><a class="nav-link active" href="quiz_history.php">Quiz History</a></li>
      <li class="nav-item"><a class="nav-link" href="reviewer.php">Reviewers</a></li>
    </ul>
    <div>
      <span class="me-2">Hello, <?php echo htmlspecialchars($_SESSION['user']['username']); ?></span>
      <a class="btn btn-outline-light btn-sm" href="logout.php">Logout</a>
    </div>
  </div>
</nav>

<div class="container">
  <div class="welcome-banner">
    <i class="bi bi-clock-history"></i>
    <h1 class="display-6 fw-bold">Your Quiz History</h1>
    <p class="lead mb-0">View your past quiz attempts and results.</p>
  </div>

  <div class="row">
    <!-- Attempts List -->
    <div class="col-12">
      <h3 class="mb-3">Past Attempts</h3>
      <?php if (empty($attempts)): ?>
        <p class="text-muted">You haven't taken any quizzes yet.</p>
      <?php else: ?>
        <div class="accordion" id="attemptsAccordion">
          <?php foreach ($attempts as $attempt): ?>
            <div class="card">
              <div class="card-header" id="heading<?php echo $attempt['id']; ?>">
                <span><?php echo htmlspecialchars($attempt['title']); ?> - Score: <?php echo $attempt['score']; ?> - Taken on: <?php echo date('Y-m-d H:i:s', strtotime($attempt['date_taken'])); ?></span>
                <div>
                  <a href="take_quiz.php?quiz_id=<?php echo $attempt['quiz_id']; ?>" class="btn btn-sm btn-primary me-1">Retake</a>
                  <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $attempt['id']; ?>" aria-expanded="false" aria-controls="collapse<?php echo $attempt['id']; ?>">
                    View Details
                  </button>
                </div>
              </div>
              <div id="collapse<?php echo $attempt['id']; ?>" class="collapse" aria-labelledby="heading<?php echo $attempt['id']; ?>" data-bs-parent="#attemptsAccordion">
                <div class="card-body">
                  <!-- Add more details here if needed, e.g., individual question results -->
                  <p>Quiz Title: <?php echo htmlspecialchars($attempt['title']); ?></p>
                  <p>Score: <?php echo $attempt['score']; ?></p>
                  <p>Date Taken: <?php echo date('Y-m-d H:i:s', strtotime($attempt['date_taken'])); ?></p>
                  <!-- You can expand this with more info from the attempt -->
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
