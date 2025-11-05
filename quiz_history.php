<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user']) || $_SESSION['user']['user_type'] !== 'teacher') {
    header('Location: index.php?path=login');
    exit;
}

try {
    $pdo = new PDO('mysql:host=localhost;dbname=quizhut_db', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
}

$searchQuery = $_GET['search'] ?? '';

$stmt = $pdo->prepare("
    SELECT id, title, description
    FROM quizzes
    WHERE teacher_id = :teacher_id
      AND (title LIKE :search OR description LIKE :search)
    ORDER BY id DESC
");
$stmt->execute([
    ':teacher_id' => $_SESSION['user']['id'],
    ':search' => "%$searchQuery%"
]);
$quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    .navbar, .btn-primary { background-color: #6f42c1 !important; border-color: #6f42c1 !important; }
    .btn-primary:hover { background-color: #5a32a3 !important; border-color: #5a32a3 !important; }
    .navbar a, .navbar-brand { color: white !important; }
    .welcome-banner { background: #6f42c1; color:white; border-radius:10px; padding:2rem; text-align:center; margin-bottom:2rem; }
    .card-dashboard {
      border-radius: 10px;
      padding: 1.5rem;
      text-align: center;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .card-dashboard i { font-size: 2.5rem; color:#6f42c1; margin-bottom: 10px; }
  </style>
</head>
<body class="bg-light">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold" href="teacher_dashboard.php">QuizHut</a>
    <ul class="navbar-nav me-auto">
      <li class="nav-item"><a class="nav-link" href="quiz_create.php">Create Quiz</a></li>
      <li class="nav-item"><a class="nav-link" href="quiz_edit.php">Edit Quiz</a></li>
      <li class="nav-item"><a class="nav-link active" href="quiz_history.php">Quiz Results</a></li>
    </ul>
    <span class="text-white me-3">Hello, <?php echo $_SESSION['user']['first_name']; ?></span>
    <a class="btn btn-outline-light btn-sm" href="logout.php">Logout</a>
  </div>
</nav>

<div class="container py-5">

  <div class="welcome-banner">
    <h2 class="fw-bold">View Quiz Results</h2>
    <p>Manage and review student quiz performances.</p>
  </div>

  <form method="GET" class="d-flex mb-3">
    <input type="text" name="search" class="form-control me-2" placeholder="Search quizzes..." value="<?php echo htmlspecialchars($searchQuery); ?>">
    <button class="btn btn-primary">Search</button>
  </form>

  <div class="row g-3">
    <?php if (!$quizzes): ?>
      <p class="text-center">No quizzes found.</p>
    <?php else: foreach ($quizzes as $quiz): ?>
      <div class="col-md-4">
        <div class="card-dashboard">
          <i class="bi bi-journal-text"></i> <!-- ✅ RESTORED ICON -->
          <h5><?php echo $quiz['title']; ?></h5>
          <p><?php echo $quiz['description']; ?></p>

          <button class="btn btn-primary w-100 mt-2" onclick="showAttempts(<?php echo $quiz['id']; ?>)">
            View Attempts
          </button>
        </div>
      </div>
    <?php endforeach; endif; ?>
  </div>

</div>

<!-- Modal -->
<div class="modal fade" id="attemptsModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Quiz Attempts</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="attemptsContent">Loading...</div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function showAttempts(id){
  fetch("fetch_attempts.php?quiz_id=" + id)
    .then(r => r.text())
    .then(html => {
      document.getElementById("attemptsContent").innerHTML = html;
      new bootstrap.Modal(document.getElementById('attemptsModal')).show();
    });
}
</script>
</body>
</html>
