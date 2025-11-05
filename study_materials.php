<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ensure user is logged in as teacher, student, or admin
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['user_type'], ['teacher', 'student', 'admin'])) {
    header('Location: login.php');
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
$lessonSql = "SELECT id, title, description, filename, author, author_id, created_at, 'Lesson' AS type FROM lessons";
if (!empty($searchQuery)) {
    $lessonSql .= " WHERE title LIKE :search OR description LIKE :search";
}
$stmt = $pdo->prepare($lessonSql);
if (!empty($searchQuery)) {
    $stmt->execute([':search' => "%$searchQuery%"]);
} else {
    $stmt->execute();
}
$lessons = $stmt->fetchAll();

// Fetch reviewers
$reviewerSql = "SELECT id, title, description, filename, author, author_id, created_at, 'Reviewer' AS type FROM reviewers";
if (!empty($searchQuery)) {
    $reviewerSql .= " WHERE title LIKE :search OR description LIKE :search";
}
$stmt = $pdo->prepare($reviewerSql);
if (!empty($searchQuery)) {
    $stmt->execute([':search' => "%$searchQuery%"]);
} else {
    $stmt->execute();
}
$reviewers = $stmt->fetchAll();

// Merge both arrays
$materials = array_merge($lessons, $reviewers);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Study Materials - QuizHut</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    .card, .btn-success { border-radius: 0.5rem; }
    .text-success { color: #198754 !important; }
    .btn-success { background-color: #198754 !important; border-color: #198754 !important; }
    .btn-primary { background-color: #0d6efd !important; border-color: #0d6efd !important; color: white !important; }
    .badge { font-size: 0.75rem; margin: 0 2px; }
  </style>
</head>
<body class="bg-light">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-success shadow-sm">
  <div class="container">
    <a class="navbar-brand text-white fw-bold" href="admin_dashboard.php">QuizHut</a>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link text-white" href="account_management.php">Accounts</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="study_materials.php">Materials</a></li>
      </ul>
      <span class="me-2 text-white">Hello, <?php echo htmlspecialchars($_SESSION['user']['username']); ?></span>
      <a class="btn btn-outline-light btn-sm" href="logout.php">Logout</a>
    </div>
  </div>
</nav>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-12">
      <div class="card p-4 mb-4 shadow-sm text-center">
        <i class="bi bi-folder2-open fs-1 text-success mb-2"></i>
        <h2 class="text-success fw-bold">Study Materials</h2>
        <p class="lead">Browse all lessons and reviewers available for your courses.</p>
      </div>
    </div>
  </div>

  <!-- Search -->
  <form method="GET" class="d-flex mb-4">
    <input type="text" name="search" class="form-control me-2" placeholder="Search lessons or reviewers..." value="<?php echo htmlspecialchars($searchQuery); ?>">
    <button type="submit" class="btn btn-success">Search</button>
  </form>

  <!-- Materials List -->
  <div class="row g-3">
    <?php if (empty($materials)): ?>
      <p class="text-center">No materials found matching your search.</p>
    <?php else: ?>
      <?php foreach ($materials as $mat): ?>
        <div class="col-12 col-md-6 mb-3">
          <div class="card shadow-sm p-4 text-center">
            <i class="bi bi-journal-text fs-1 text-success mb-2"></i>
            <h5 class="card-title"><?php echo htmlspecialchars($mat['title']); ?></h5>
            <p class="card-text"><?php echo htmlspecialchars($mat['description']); ?></p>
            <p class="card-text"><i class="bi bi-person-circle me-1"></i><?php echo htmlspecialchars($mat['author']); ?></p>
            <?php if(isset($mat['created_at'])): ?>
              <p class="card-text"><small class="text-muted"><?php echo htmlspecialchars($mat['created_at']); ?></small></p>
            <?php endif; ?>

            <!-- Tags -->
            <div class="mb-2">
              <span class="badge bg-primary"><?php echo ucfirst($_SESSION['user']['user_type']); ?></span>
              <span class="badge bg-secondary"><?php echo htmlspecialchars($mat['type']); ?></span>
            </div>

            <a href="uploads/<?php echo htmlspecialchars($mat['filename']); ?>" class="btn btn-success w-100 mb-1" download>Download</a>
            <a href="delete_material.php?id=<?php echo $mat['id']; ?>" class="btn btn-danger w-100 mb-1">Delete</a>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
