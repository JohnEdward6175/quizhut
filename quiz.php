<?php
// quiz.php — Student Quiz List with Modal Records

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['user_type'], ['student', 'teacher'])) {
    header("Location: login.php");
    exit;
}

// MySQL setup
$host = 'localhost';
$db   = 'quizhut_db';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$dbError = '';
$quizzes = [];

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // ✅ Fetch quizzes with description
    $stmt = $pdo->query("SELECT id, title, description FROM quizzes ORDER BY id DESC");
    $quizzes = $stmt->fetchAll();

} catch (PDOException $e) {
    $dbError = "Database error: " . htmlspecialchars($e->getMessage());
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Available Quizzes - QuizHut</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      background-color: #f8f9fa;
    }
    .navbar, .btn-primary {
      background-color: #0d6efd !important;
    }
    .navbar .navbar-brand, .navbar a, .navbar span {
      color: white !important;
    }
    .quiz-card {
      transition: all 0.2s ease-in-out;
      border-radius: 0.5rem;
      height: 100%;
    }
    .quiz-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
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
    }
    .quiz-desc {
      color: #6c757d;
      font-size: 0.9rem;
    }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold" href="student_dashboard.php">QuizHut</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="lessons.php">Access Lessons</a></li>
        <li class="nav-item"><a class="nav-link active" href="quiz.php">Take a Quiz</a></li>
        <li class="nav-item"><a class="nav-link" href="reviewer.php">Reviewers</a></li>
      </ul>
      <a class="btn btn-outline-light btn-sm" href="logout.php">Logout</a>
    </div>
  </div>
</nav>

<!-- Content -->
<div class="container py-5">
  <div class="welcome-banner">
    <i class="bi bi-journal-text"></i>
    <div>
      <h1 class="fw-bold mb-1">Available Quizzes</h1>
      <p class="mb-0">Select a quiz to test your knowledge or view your past attempts.</p>
    </div>
  </div>

  <?php if (!empty($dbError)): ?>
    <div class="alert alert-danger text-center"><?php echo $dbError; ?></div>
  <?php elseif (empty($quizzes)): ?>
    <div class="alert alert-warning text-center">No quizzes available at the moment.</div>
  <?php else: ?>
    <div class="row g-4">
      <?php foreach ($quizzes as $quiz): ?>
        <?php
          // Check if student has attempted this quiz
          $attempted = false;
          try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM quiz_attempts WHERE quiz_id = ? AND student_id = ?");
            $stmt->execute([$quiz['id'], $_SESSION['user']['id']]);
            $attempted = $stmt->fetchColumn() > 0;
          } catch (PDOException $e) {
            // Ignore error, assume not attempted
          }
        ?>
        <div class="col-md-6 col-lg-4">
          <div class="card quiz-card shadow-sm h-100">
            <div class="card-body d-flex flex-column justify-content-between">
              <div>
                <h5 class="card-title mb-2"><?php echo htmlspecialchars($quiz['title']); ?></h5>
                <?php if (!empty($quiz['description'])): ?>
                  <p class="quiz-desc mb-3"><?php echo nl2br(htmlspecialchars($quiz['description'])); ?></p>
                <?php endif; ?>
              </div>
              <div class="d-flex justify-content-between align-items-center mt-auto">
                <small class="text-muted">Quiz ID: <?php echo $quiz['id']; ?></small>
                <div class="d-flex gap-2">
                  <a href="take_quiz.php?quiz_id=<?= urlencode($quiz['id']) ?>" class="btn btn-primary btn-sm"><?php echo $attempted ? 'Retake' : 'Take'; ?></a>

                  <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#recordsModal" data-quiz-id="<?php echo $quiz['id']; ?>">
                    <i class="bi bi-clock-history"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<!-- Records Modal -->
<div class="modal fade" id="recordsModal" tabindex="-1" aria-labelledby="recordsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="recordsModalLabel">Your Quiz Records</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="recordsContent">
        <div class="text-center py-3">
          <div class="spinner-border text-primary" role="status"></div>
          <p class="mt-2 mb-0">Loading records...</p>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Fetch quiz records dynamically
const recordsModal = document.getElementById('recordsModal');
recordsModal.addEventListener('show.bs.modal', event => {
  const button = event.relatedTarget;
  const quizId = button.getAttribute('data-quiz-id');
  const content = document.getElementById('recordsContent');

  content.innerHTML = `
    <div class="text-center py-3">
      <div class="spinner-border text-primary" role="status"></div>
      <p class="mt-2 mb-0">Loading records...</p>
    </div>`;

  fetch('fetch_quiz_records.php?quiz_id=' + quizId)
    .then(res => res.text())
    .then(html => {
      content.innerHTML = html;
    })
    .catch(() => {
      content.innerHTML = `<div class="alert alert-danger text-center">Failed to load records.</div>`;
    });
});
</script>
</body>
</html>
