<?php
// reviewer_upload.php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Only allow teachers or students
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['user_type'], ['teacher', 'student'])) {
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

// Handle file upload
$uploadMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['reviewer_file'])) {
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $fileName = basename($_FILES['reviewer_file']['name']);
    $targetFile = $uploadDir . $fileName;
    $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    $allowedTypes = ['pdf', 'doc', 'docx', 'txt'];

    if (in_array($fileType, $allowedTypes) && move_uploaded_file($_FILES['reviewer_file']['tmp_name'], $targetFile)) {
        // Insert into database
        $stmt = $pdo->prepare("INSERT INTO reviewers (title, description, filename, author, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([
            $_POST['reviewer_title'] ?? 'Untitled',
            $_POST['reviewer_description'] ?? 'No description.',
            $fileName,
            $_SESSION['user']['username'] ?? 'Anonymous'
        ]);
        $uploadMessage = "Reviewer uploaded successfully!";
    } else {
        $uploadMessage = "Error uploading file. Allowed types: PDF, DOC, DOCX, TXT.";
    }
}

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_reviewer_id'])) {
    $deleteId = (int)$_POST['delete_reviewer_id'];
    // Get file name
    $stmt = $pdo->prepare("SELECT filename FROM reviewers WHERE id = ?");
    $stmt->execute([$deleteId]);
    $file = $stmt->fetchColumn();
    if ($file && file_exists('uploads/' . $file)) unlink('uploads/' . $file);
    // Delete from DB
    $stmt = $pdo->prepare("DELETE FROM reviewers WHERE id = ?");
    $stmt->execute([$deleteId]);
    header("Location: reviewer_upload.php");
    exit;
}

// Handle search
$searchQuery = $_GET['search'] ?? '';
$sql = "SELECT * FROM reviewers WHERE 1";
$params = [];

if (!empty($searchQuery)) {
    $sql .= " AND (title LIKE :search OR description LIKE :search OR author LIKE :search)";
    $params[':search'] = "%$searchQuery%";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reviewers = $stmt->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Reviewer Upload - QuizHut</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    .navbar, .btn-primary { background-color: #0d6efd !important; }
    .navbar .navbar-brand, .navbar a { color: white !important; }
    .card-dashboard { border-radius: 0.5rem; min-height: 200px; }
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
  <div class="card p-4 mb-4 text-center shadow-sm">
    <i class="bi bi-pencil-square fs-1 text-primary mb-2"></i>
    <h2 class="fw-bold">Reviewer Upload</h2>
    <p class="lead">Upload and share reviewers for practice.</p>
  </div>

  <!-- Search -->
  <form method="GET" class="d-flex mb-3">
    <input type="text" name="search" class="form-control me-2" placeholder="Search reviewers..." value="<?php echo htmlspecialchars($searchQuery); ?>">
    <button type="submit" class="btn btn-primary">Search</button>
  </form>

  <!-- Upload Form -->
  <form method="POST" enctype="multipart/form-data" class="mb-4">
    <div class="row g-2">
      <div class="col-md-4"><input type="text" name="reviewer_title" class="form-control" placeholder="Title" required></div>
      <div class="col-md-4"><input type="text" name="reviewer_description" class="form-control" placeholder="Description" required></div>
      <div class="col-md-3"><input type="file" name="reviewer_file" class="form-control" accept=".pdf,.doc,.docx,.txt" required></div>
      <div class="col-md-1"><button type="submit" class="btn btn-success w-100">Upload</button></div>
    </div>
    <?php if ($uploadMessage): ?>
      <p class="mt-2 text-<?php echo strpos($uploadMessage,'Error')===false ? 'success':'danger'; ?>"><?php echo $uploadMessage; ?></p>
    <?php endif; ?>
  </form>

  <!-- Reviewers List -->
  <div class="row g-3">
    <?php if (empty($reviewers)): ?>
      <p class="text-center">No reviewers found.</p>
    <?php else: ?>
      <?php foreach ($reviewers as $rev): ?>
        <div class="col-12 col-md-6 col-lg-4">
          <div class="card card-dashboard text-center p-4">
            <i class="bi bi-pencil-square fs-1 text-primary mb-2"></i>
            <h5 class="card-title"><?php echo htmlspecialchars($rev['title']); ?></h5>
            <p class="card-text"><?php echo htmlspecialchars($rev['description']); ?></p>
            <p class="card-text"><i class="bi bi-person-circle me-1"></i><?php echo htmlspecialchars($rev['author']); ?></p>
            <p class="card-text"><small class="text-muted"><?php echo htmlspecialchars($rev['created_at']); ?></small></p>
            <a href="uploads/<?php echo htmlspecialchars($rev['filename']); ?>" class="btn btn-primary w-100 mb-1" download>Download</a>
            <form method="POST" class="d-inline">
              <input type="hidden" name="delete_reviewer_id" value="<?php echo $rev['id']; ?>">
              <button type="submit" class="btn btn-danger w-100">Delete</button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
