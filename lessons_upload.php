<?php
// lessons_upload.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Only allow teachers
if (!isset($_SESSION['user']) || $_SESSION['user']['user_type'] !== 'teacher') {
    header('Location: index.php?path=login');
    exit;
}

// Database connection
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "quizhut_db";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// Ensure uploads directory exists
$uploadDir = 'uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// ✅ DELETE LESSON
if (isset($_POST['delete_id'])) {
    $deleteId = intval($_POST['delete_id']);

    // Get filename first
    $fileQuery = $conn->query("SELECT filename FROM lessons WHERE id = $deleteId");
    if ($fileQuery && $fileQuery->num_rows > 0) {
        $fileRow = $fileQuery->fetch_assoc();
        $filePath = $uploadDir . $fileRow['filename'];

        // Delete DB entry
        $conn->query("DELETE FROM lessons WHERE id = $deleteId");

        // Delete actual file if exists
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
    header("Location: lessons_upload.php");
    exit;
}

// Handle file upload
$uploadMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['lesson_file'])) {
    $fileName = basename($_FILES['lesson_file']['name']);
    $targetFile = $uploadDir . $fileName;
    $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    $allowedTypes = ['pdf', 'doc', 'docx', 'txt'];

    if (in_array($fileType, $allowedTypes) && move_uploaded_file($_FILES['lesson_file']['tmp_name'], $targetFile)) {
        
        $title = $conn->real_escape_string($_POST['lesson_title']);
        $description = $conn->real_escape_string($_POST['lesson_description']);
        $author = $conn->real_escape_string($_SESSION['user']['username']);

        $conn->query("INSERT INTO lessons (title, description, filename, author) 
                      VALUES ('$title', '$description', '$fileName', '$author')");

        $uploadMessage = "✅ Lesson uploaded successfully!";
    } else {
        $uploadMessage = "❌ Error uploading file. Allowed types: PDF, DOC, DOCX, TXT.";
    }
}

// Handle search
$searchQuery = $_GET['search'] ?? '';
$searchSQL = $conn->real_escape_string($searchQuery);

$result = $conn->query("SELECT * FROM lessons 
                        WHERE title LIKE '%$searchSQL%' 
                        OR description LIKE '%$searchSQL%' 
                        ORDER BY id DESC");
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Lesson Upload - QuizHut</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    .navbar, .btn-primary {
      background-color: #6f42c1 !important;
      border-color: #6f42c1 !important;
    }
    .btn-primary:hover { background-color: #5a32a3 !important; border-color: #5a32a3 !important; }
    .navbar .navbar-brand, .navbar a, .navbar span { color: white !important; }
    .card-dashboard {
      border-radius: 0.75rem;
      min-height: 200px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
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
    .back-icon {
      position: absolute;
      top: 1rem;
      left: 1rem;
      color: white;
      font-size: 1.5rem;
      text-decoration: none;
    }
  </style>
</head>
<body class="bg-light">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold" href="teacher_dashboard.php">QuizHut</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="lessons_upload.php">Lessons</a></li>
        <li class="nav-item"><a class="nav-link" href="quiz_upload.php">Quiz</a></li>
        <li class="nav-item"><a class="nav-link" href="reviewer_upload.php">Reviewer</a></li>
      </ul>
      <a class="btn btn-outline-light btn-sm" href="home.php">Logout</a>
    </div>
  </div>
</nav>

<div class="container py-5">

  <div class="welcome-banner">
    <a href="teacher_dashboard.php" class="back-icon"><i class="bi bi-arrow-left-circle"></i></a>
    <h2 class="fw-bold">Lesson Creation & Upload</h2>
  </div>

  <form method="GET" class="d-flex mb-3">
    <input type="text" name="search" class="form-control me-2" placeholder="Search lessons..." value="<?php echo htmlspecialchars($searchQuery); ?>">
    <button type="submit" class="btn btn-primary">Search</button>
  </form>

  <form method="POST" enctype="multipart/form-data" class="mb-4">
    <div class="row g-2">
      <div class="col-md-4"><input type="text" name="lesson_title" class="form-control" placeholder="Lesson Title" required></div>
      <div class="col-md-4"><input type="text" name="lesson_description" class="form-control" placeholder="Description" required></div>
      <div class="col-md-3"><input type="file" name="lesson_file" class="form-control" accept=".pdf,.doc,.docx,.txt" required></div>
      <div class="col-md-1"><button type="submit" class="btn btn-success w-100">Upload</button></div>
    </div>
    <?php if ($uploadMessage): ?>
      <p class="mt-2 fw-bold <?php echo strpos($uploadMessage, '❌') === false ? 'text-success' : 'text-danger'; ?>">
        <?php echo $uploadMessage; ?>
      </p>
    <?php endif; ?>
  </form>

  <div class="row g-3">
    <?php if ($result->num_rows == 0): ?>
      <p class="text-center">No lessons found.</p>
    <?php else: ?>
      <?php while($lesson = $result->fetch_assoc()): ?>
        <div class="col-12 col-md-6 col-lg-4">
          <div class="card card-dashboard text-center p-4">
            <i class="bi bi-journal-text fs-1"></i>
            <h5 class="card-title"><?php echo htmlspecialchars($lesson['title']); ?></h5>
            <p class="card-text"><?php echo htmlspecialchars($lesson['description']); ?></p>
            <p class="card-text"><i class="bi bi-person-circle me-1"></i><?php echo htmlspecialchars($lesson['author']); ?></p>

            <a href="uploads/<?php echo htmlspecialchars($lesson['filename']); ?>" class="btn btn-primary mb-2" download>Download</a>

            <!-- ✅ DELETE BUTTON -->
            <form method="POST" onsubmit="return confirm('Delete this lesson? This cannot be undone.');">
              <input type="hidden" name="delete_id" value="<?php echo $lesson['id']; ?>">
              <button type="submit" class="btn btn-danger w-100">Delete</button>
            </form>

          </div>
        </div>
      <?php endwhile; ?>
    <?php endif; ?>
  </div>

</div>

</body>
</html>
