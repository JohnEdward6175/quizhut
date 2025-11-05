<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Only allow teachers
if (!isset($_SESSION['user']) || $_SESSION['user']['user_type'] !== 'teacher') {
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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['reviewer_file']) && !isset($_POST['delete_reviewer_id'])) {
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $fileName = basename($_FILES['reviewer_file']['name']);
    $targetFile = $uploadDir . $fileName;
    $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    $allowedTypes = ['pdf', 'doc', 'docx', 'txt'];

    if (in_array($fileType, $allowedTypes) && move_uploaded_file($_FILES['reviewer_file']['tmp_name'], $targetFile)) {
        // Insert reviewer with author_id
        $stmt = $pdo->prepare("INSERT INTO reviewers (title, description, filename, author, author_type, author_id, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([
            $_POST['reviewer_title'] ?? 'Untitled',
            $_POST['reviewer_description'] ?? 'No description.',
            $fileName,
            $_SESSION['user']['username'] ?? 'Anonymous',
            'teacher',
            $_SESSION['user']['id'] ?? null
        ]);
        $uploadMessage = "Reviewer uploaded successfully!";
    } else {
        $uploadMessage = "Error uploading file. Allowed types: PDF, DOC, DOCX, TXT.";
    }
}

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_reviewer_id'])) {
    $deleteId = (int)$_POST['delete_reviewer_id'];

    // Get filename to delete
    $stmt = $pdo->prepare("SELECT filename FROM reviewers WHERE id = ?");
    $stmt->execute([$deleteId]);
    $file = $stmt->fetch();
    if ($file) {
        $filePath = 'uploads/' . $file['filename'];
        if (file_exists($filePath)) unlink($filePath);

        // Delete from database
        $stmt = $pdo->prepare("DELETE FROM reviewers WHERE id = ?");
        $stmt->execute([$deleteId]);
    }

    header("Location: reviewer_upload.php");
    exit;
}

// Handle search
$searchQuery = $_GET['search'] ?? '';
$sql = "SELECT * FROM reviewers";
$params = [];

if (!empty($searchQuery)) {
    $sql .= " WHERE title LIKE ? OR description LIKE ?";
    $params[] = "%$searchQuery%";
    $params[] = "%$searchQuery%";
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
    .navbar, .btn-primary {
      background-color: #6f42c1 !important;
      border-color: #6f42c1 !important;
    }
    .btn-primary:hover { background-color: #5a32a3 !important; border-color: #5a32a3 !important; }
    .navbar .navbar-brand, .navbar a { color: white !important; }
    .card-dashboard {
      border-radius: 0.75rem;
      min-height: 200px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .card-dashboard i { font-size: 2rem; margin-bottom: 0.5rem; color: #6f42c1; }
    .badge { font-size: 0.75rem; margin: 0 2px; }
  </style>
</head>
<body class="bg-light">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold" href="teacher_dashboard.php">QuizHut</a>
    <ul class="navbar-nav me-auto">
      <li class="nav-item"><a class="nav-link text-white" href="lessons_upload.php">Lessons</a></li>
      <li class="nav-item"><a class="nav-link text-white" href="quiz_upload.php">Quiz</a></li>
      <li class="nav-item"><a class="nav-link text-white" href="reviewer_upload.php">Reviewer</a></li>
    </ul>
    <a class="btn btn-outline-light btn-sm" href="home.php">Logout</a>
  </div>
</nav>

<div class="container py-5">
  <!-- Page Header -->
  <div class="card p-4 mb-4 text-center shadow-sm">
    <i class="bi bi-journal-text fs-1 text-white bg-purple rounded-circle p-3 mb-3" style="background-color:#6f42c1;"></i>
    <h2 class="fw-bold">Reviewer Upload</h2>
    <p class="lead">Create, upload, and share reviewers for students to practice.</p>
  </div>

  <!-- Search Bar -->
  <form method="GET" class="d-flex mb-3">
    <input type="text" name="search" class="form-control me-2" placeholder="Search reviewers..." value="<?php echo htmlspecialchars($searchQuery); ?>">
    <button type="submit" class="btn btn-primary">Search</button>
  </form>

  <!-- Upload Form -->
  <form method="POST" enctype="multipart/form-data" class="mb-4">
    <div class="row g-2">
      <div class="col-md-4"><input type="text" name="reviewer_title" class="form-control" placeholder="Reviewer Title" required></div>
      <div class="col-md-4"><input type="text" name="reviewer_description" class="form-control" placeholder="Description" required></div>
      <div class="col-md-3"><input type="file" name="reviewer_file" class="form-control" accept=".pdf,.doc,.docx,.txt" required></div>
      <div class="col-md-1"><button type="submit" class="btn btn-success w-100">Upload</button></div>
    </div>
    <?php if ($uploadMessage): ?>
      <p class="mt-2 text-<?php echo strpos($uploadMessage, 'Error') === false ? 'success' : 'danger'; ?>"><?php echo $uploadMessage; ?></p>
    <?php endif; ?>
  </form>

  <!-- Reviewers List -->
  <div class="row g-3">
    <?php if (empty($reviewers)): ?>
      <p class="text-center">No reviewers found matching your search.</p>
    <?php else: ?>
      <?php foreach ($reviewers as $reviewer): ?>
        <div class="col-12 col-md-6 col-lg-4">
          <div class="card card-dashboard text-center p-4">
            <i class="bi bi-pencil-square fs-1"></i>
            <h5 class="card-title"><?php echo htmlspecialchars($reviewer['title']); ?></h5>
            <p class="card-text"><?php echo htmlspecialchars($reviewer['description']); ?></p>
            <p class="card-text"><i class="bi bi-person-circle me-1"></i><?php echo htmlspecialchars($reviewer['author']); ?></p>
            <!-- Tags -->
            <div class="mb-2">
              <span class="badge bg-primary"><?php echo htmlspecialchars($reviewer['author_type']); ?></span>
              <span class="badge bg-secondary">Reviewer</span>
            </div>
            <a href="uploads/<?php echo htmlspecialchars($reviewer['filename']); ?>" class="btn btn-primary mb-2" download>Download</a>
            <!-- Delete Button -->
            <form method="POST" class="d-inline">
              <input type="hidden" name="delete_reviewer_id" value="<?php echo $reviewer['id']; ?>">
              <button type="submit" class="btn btn-danger">Delete</button>
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
