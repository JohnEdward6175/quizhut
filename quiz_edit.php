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

// Handle edit submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quiz_id'])) {
    $quizId = $_POST['quiz_id'];
    $title = trim($_POST['quiz_title']);
    $questions = $_POST['questions'] ?? [];

    if ($title && !empty($questions)) {
        // Update quiz title
        $stmt = $pdo->prepare("UPDATE quizzes SET title = ? WHERE id = ? AND teacher_id = ?");
        $stmt->execute([$title, $quizId, $_SESSION['user']['id']]);

        // Delete old questions
        $stmtDel = $pdo->prepare("DELETE FROM questions WHERE quiz_id = ?");
        $stmtDel->execute([$quizId]);

        // Insert updated questions
        $stmtQ = $pdo->prepare("INSERT INTO questions (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_option) VALUES (?, ?, ?, ?, ?, ?, ?)");
        foreach ($questions as $q) {
            $stmtQ->execute([
                $quizId,
                $q['question'],
                $q['A'],
                $q['B'],
                $q['C'],
                $q['D'],
                $q['answer']
            ]);
        }

        // Auto-close form by redirecting back to the page
        header("Location: quiz_edit.php");
        exit;
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $delId = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM questions WHERE quiz_id = ?");
    $stmt->execute([$delId]);
    $stmt = $pdo->prepare("DELETE FROM quizzes WHERE id = ? AND teacher_id = ?");
    $stmt->execute([$delId, $_SESSION['user']['id']]);
    header("Location: quiz_edit.php");
    exit;
}

// Fetch quizzes for this teacher
$stmt = $pdo->prepare("SELECT * FROM quizzes WHERE teacher_id = ?");
$stmt->execute([$_SESSION['user']['id']]);
$quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get selected quiz for editing
$selectedQuiz = null;
if (isset($_GET['edit'])) {
    $quizId = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM quizzes WHERE id = ? AND teacher_id = ?");
    $stmt->execute([$quizId, $_SESSION['user']['id']]);
    $selectedQuiz = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($selectedQuiz) {
        $stmt = $pdo->prepare("SELECT * FROM questions WHERE quiz_id = ?");
        $stmt->execute([$quizId]);
        $selectedQuiz['questions'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Edit Quiz - QuizHut</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    .navbar, .btn-primary { background-color: #6f42c1 !important; border-color: #6f42c1 !important; }
    .btn-primary:hover { background-color: #5a32a3 !important; border-color: #5a32a3 !important; }
    .welcome-banner { background-color: #6f42c1; color: white; border-radius: 0.5rem; padding: 2rem 1rem; text-align: center; margin-bottom: 2rem; }
    .card { border-radius: 0.75rem; box-shadow: 0 4px 12px rgba(0,0,0,0.1); margin-bottom: 1rem; }
    .card-header { display: flex; justify-content: space-between; align-items: center; }
    .navbar .navbar-brand, .navbar a, .navbar span { color: white !important; }
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
    <i class="bi bi-pencil-square"></i>
    <h1 class="display-6 fw-bold">Edit Existing Quizzes</h1>
    <p class="lead mb-0">Select a quiz to modify or delete.</p>
  </div>

  <div class="row">
    <!-- Quizzes List -->
    <div class="col-12 col-md-6">
      <h3 class="mb-3">Your Quizzes</h3>
      <div class="accordion" id="quizAccordion">
        <?php foreach ($quizzes as $quiz): ?>
          <div class="card">
            <div class="card-header" id="heading<?php echo $quiz['id']; ?>">
              <span><?php echo htmlspecialchars($quiz['title']); ?></span>
              <div>
                <a href="?edit=<?php echo $quiz['id']; ?>" class="btn btn-sm btn-primary me-1">Edit</a>
                <a href="?delete=<?php echo $quiz['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Edit Form -->
    <div class="col-12 col-md-6">
      <h3 class="mb-3">Edit Quiz</h3>
      <?php if ($selectedQuiz): ?>
        <form method="POST">
          <input type="hidden" name="quiz_id" value="<?php echo $selectedQuiz['id']; ?>">
          <div class="mb-3">
            <label class="form-label">Quiz Title</label>
            <input type="text" class="form-control" name="quiz_title" value="<?php echo htmlspecialchars($selectedQuiz['title']); ?>" required>
          </div>
          <div id="questions_container">
            <?php foreach ($selectedQuiz['questions'] as $i => $q): ?>
              <div class="question-block mb-3 border p-3 rounded">
                <label class="form-label fw-bold">Question <?php echo $i + 1; ?></label>
                <input type="text" name="questions[<?php echo $i; ?>][question]" class="form-control mb-2" value="<?php echo htmlspecialchars($q['question_text']); ?>" required>
                <input type="text" name="questions[<?php echo $i; ?>][A]" class="form-control mb-1" value="<?php echo htmlspecialchars($q['option_a']); ?>" required>
                <input type="text" name="questions[<?php echo $i; ?>][B]" class="form-control mb-1" value="<?php echo htmlspecialchars($q['option_b']); ?>" required>
                <input type="text" name="questions[<?php echo $i; ?>][C]" class="form-control mb-1" value="<?php echo htmlspecialchars($q['option_c']); ?>" required>
                <input type="text" name="questions[<?php echo $i; ?>][D]" class="form-control mb-1" value="<?php echo htmlspecialchars($q['option_d']); ?>" required>
                <select name="questions[<?php echo $i; ?>][answer]" class="form-select mt-1" required>
                  <option value="">Select Correct Answer</option>
                  <option value="A" <?php echo $q['correct_option']=='A'?'selected':''; ?>>A</option>
                  <option value="B" <?php echo $q['correct_option']=='B'?'selected':''; ?>>B</option>
                  <option value="C" <?php echo $q['correct_option']=='C'?'selected':''; ?>>C</option>
                  <option value="D" <?php echo $q['correct_option']=='D'?'selected':''; ?>>D</option>
                </select>
              </div>
            <?php endforeach; ?>
          </div>
          <button type="button" class="btn btn-secondary mb-3" id="add_question">Add Another Question</button>
          <button type="submit" class="btn btn-primary w-100">Save Changes</button>
        </form>
      <?php else: ?>
        <p class="text-muted">Select a quiz from the list to edit.</p>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
let questionCount = <?php echo $selectedQuiz ? count($selectedQuiz['questions']) : 1; ?>;
document.getElementById('add_question')?.addEventListener('click', function() {
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
