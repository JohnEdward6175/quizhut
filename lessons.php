<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Only allow logged-in students or guest students
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['user_type'], ['student', 'guest'])) {
    header("Location: login.php");
    exit;
}

// Database connection
$host = 'localhost';
$db   = 'quizhut_db';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Handle search
$searchQuery = $_GET['search'] ?? '';

// Fetch lessons
$sql = "SELECT id, title, description, filename, author, created_at FROM lessons";
$params = [];

if (!empty($searchQuery)) {
    $sql .= " WHERE title LIKE ? OR description LIKE ?";
    $params[] = "%$searchQuery%";
    $params[] = "%$searchQuery%";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$lessons = $stmt->fetchAll();
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Lessons - QuizHut</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    .navbar, .btn-primary {
      background-color: #0d6efd !important;
    }
    .navbar .navbar-brand, .navbar a, .navbar span { color: white !important; }
    .card { border-radius: 0.5rem; min-height: 220px; }
  </style>
</head>
<body class="bg-light">


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

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-12">
      <div class="card p-4 mb-4 text-center shadow-sm">
        <i class="bi bi-book fs-1 text-primary mb-2"></i>
        <h2 class="fw-bold text-primary">Available Lessons</h2>
        <p class="lead">Browse all lessons uploaded for your courses.</p>
      </div>
    </div>
  </div>

  <!-- Search -->
  <form method="GET" class="d-flex mb-4">
    <input type="text" name="search" class="form-control me-2" placeholder="Search lessons..." value="<?php echo htmlspecialchars($searchQuery); ?>">
    <button type="submit" class="btn btn-primary">Search</button>
  </form>

  <!-- Lessons List -->
  <div class="row g-3">
    <?php if (empty($lessons)): ?>
      <p class="text-center">No lessons found matching your search.</p>
    <?php else: ?>
      <?php foreach ($lessons as $lesson): ?>
        <div class="col-12 col-md-6 col-lg-4">
          <div class="card p-4 text-center shadow-sm">
            <i class="bi bi-journal-text fs-1 text-primary mb-2"></i>
            <h5 class="card-title"><?php echo htmlspecialchars($lesson['title']); ?></h5>
            <p class="card-text"><?php echo htmlspecialchars($lesson['description']); ?></p>
            <p class="card-text"><i class="bi bi-person-circle me-1"></i><?php echo htmlspecialchars($lesson['author']); ?></p>
            <?php if(isset($lesson['created_at'])): ?>
              <p class="card-text"><small class="text-muted"><?php echo htmlspecialchars($lesson['created_at']); ?></small></p>
            <?php endif; ?>
            <a href="uploads/<?php echo htmlspecialchars($lesson['filename']); ?>" class="btn btn-primary w-100 mt-2" download>Download Lesson</a>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
