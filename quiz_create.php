<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ensure user is a teacher
if (!isset($_SESSION['user']) || $_SESSION['user']['user_type'] !== 'teacher') {
    header('Location: index.php?path=login');
    exit;
}

// Connect to database
try {
    $pdo = new PDO('mysql:host=localhost;dbname=quizhut_db', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

$createMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['quiz_title']);
    $questions = $_POST['questions'] ?? [];

    $valid = true;
    foreach ($questions as $q) {
        if (empty($q['question']) || empty($q['A']) || empty($q['B']) || empty($q['C']) || empty($q['D']) || empty($q['answer'])) {
            $valid = false;
            break;
        }
    }

    if ($title && $valid) {
        // Insert quiz
        $stmt = $pdo->prepare("INSERT INTO quizzes (title, teacher_id) VALUES (?, ?)");
        $stmt->execute([$title, $_SESSION['user']['id']]);
        $quiz_id = $pdo->lastInsertId();

        // Insert questions
        $stmtQ = $pdo->prepare("INSERT INTO questions (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_option) VALUES (?, ?, ?, ?, ?, ?, ?)");
        foreach ($questions as $q) {
            $stmtQ->execute([
                $quiz_id,
                $q['question'],
                $q['A'],
                $q['B'],
                $q['C'],
                $q['D'],
                $q['answer']
            ]);
        }

        $createMessage = "Quiz created successfully!";
    } else {
        $createMessage = "Please fill in all fields for all questions.";
    }
}

// Fetch quizzes created by this teacher
$stmt = $pdo->prepare("SELECT * FROM quizzes WHERE teacher_id = ?");
$stmt->execute([$_SESSION['user']['id']]);
$quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Create Quiz - QuizHut</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
.navbar, .btn-primary { background-color: #6f42c1 !important; border-color: #6f42c1 !important; }
.btn-primary:hover { background-color: #5a32a3 !important; border-color: #5a32a3 !important; }
.welcome-banner { background-color: #6f42c1; color: white; border-radius: 0.5rem; padding: 2rem 1rem; text-align: center; margin-bottom: 2rem; }
.card { border-radius: 0.75rem; box-shadow: 0 4px 12px rgba(0,0,0,0.1); margin-bottom: 1rem; }
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
      <li class="nav-item"><a class="nav-link" href="quiz_history.php">Quiz Results</a></li>
    </ul>
    <div>
      <span class="me-2">Hello, <?php echo htmlspecialchars($_SESSION['user']['username']); ?></span>
      <a class="btn btn-outline-light btn-sm" href="home.php">Logout</a>
    </div>
  </div>
</nav>

<div class="container">
  <div class="welcome-banner">
    <i class="bi bi-plus-circle"></i>
    <h1 class="display-6 fw-bold">Create New Quiz</h1>
    <p class="lead mb-0">Enter multiple-choice questions and select the correct answer.</p>
  </div>

  <div class="row">
    <!-- Create Form -->
    <div class="col-12 col-md-6">
      <?php if ($createMessage): ?>
        <div class="alert alert-info"><?php echo htmlspecialchars($createMessage); ?></div>
      <?php endif; ?>
      <form method="POST">
        <div class="mb-3">
          <label class="form-label">Quiz Title</label>
          <input type="text" name="quiz_title" class="form-control" required>
        </div>
        <div id="questions_container">
          <div class="question-block mb-3 border p-3 rounded">
            <label class="form-label fw-bold">Question 1</label>
            <input type="text" name="questions[0][question]" class="form-control mb-2" placeholder="Question" required>
            <input type="text" name="questions[0][A]" class="form-control mb-1" placeholder="Option A" required>
            <input type="text" name="questions[0][B]" class="form-control mb-1" placeholder="Option B" required>
            <input type="text" name="questions[0][C]" class="form-control mb-1" placeholder="Option C" required>
            <input type="text" name="questions[0][D]" class="form-control mb-1" placeholder="Option D" required>
            <select name="questions[0][answer]" class="form-select mt-1" required>
              <option value="">Select Correct Answer</option>
              <option value="A">A</option>
              <option value="B">B</option>
              <option value="C">C</option>
              <option value="D">D</option>
            </select>
          </div>
        </div>
        <button type="button" class="btn btn-secondary mb-3" id="add_question">Add Another Question</button>
        <button type="submit" class="btn btn-primary w-100">Create Quiz</button>
      </form>
    </div>

    <!-- Existing Quizzes -->
    <div class="col-12 col-md-6">
      <h3>Your Existing Quizzes</h3>
      <div class="row g-3">
        <?php foreach ($quizzes as $quiz): ?>
          <div class="col-12">
            <div class="card p-3">
              <h5 class="card-title"><?php echo htmlspecialchars($quiz['title']); ?></h5>
              <p class="card-text">Quiz ID: <?php echo $quiz['id']; ?></p>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<script>
let questionCount = 1;
document.getElementById('add_question').addEventListener('click', function() {
    const container = document.getElementById('questions_container');
    const div = document.createElement('div');
    div.classList.add('question-block', 'mb-3', 'border', 'p-3', 'rounded');
    div.innerHTML = `
      <label class="form-label fw-bold">Question ${questionCount + 1}</label>
      <input type="text" name="questions[${questionCount}][question]" class="form-control mb-2" placeholder="Question" required>
      <input type="text" name="questions[${questionCount}][A]" class="form-control mb-1" placeholder="Option A" required>
      <input type="text" name="questions[${questionCount}][B]" class="form-control mb-1" placeholder="Option B" required>
      <input type="text" name="questions[${questionCount}][C]" class="form-control mb-1" placeholder="Option C" required>
      <input type="text" name="questions[${questionCount}][D]" class="form-control mb-1" placeholder="Option D" required>
      <select name="questions[${questionCount}][answer]" class="form-select mt-1" required>
        <option value="">Select Correct Answer</option>
        <option value="A">A</option>
        <option value="B">B</option>
        <option value="C">C</option>
        <option value="D">D</option>
      </select>
    `;
    container.appendChild(div);
    questionCount++;
});
</script>

</body>
</html>
