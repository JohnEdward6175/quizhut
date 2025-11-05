<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// require login as student
if (!isset($_SESSION['user']) || $_SESSION['user']['user_type'] !== 'student') {
    header("Location: login.php");
    exit;
}

// accept multiple possible GET parameter names
$quizId = 0;
if (!empty($_GET['id'])) {
    $quizId = (int) $_GET['id'];
} elseif (!empty($_GET['quiz_id'])) {
    $quizId = (int) $_GET['quiz_id'];
} elseif (!empty($_GET['quizId'])) {
    $quizId = (int) $_GET['quizId'];
}

if ($quizId <= 0) {
    die("No quiz selected.");
}

// Database connection
$host = 'localhost';
$db   = 'quizhut_db';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Database error: " . htmlspecialchars($e->getMessage()));
}

// fetch quiz record
$stmt = $pdo->prepare("SELECT * FROM quizzes WHERE id = ?");
$stmt->execute([$quizId]);
$quiz = $stmt->fetch();

if (!$quiz) {
    die("Quiz not found.");
}

// fetch questions
$stmt = $pdo->prepare("SELECT * FROM questions WHERE quiz_id = ? ORDER BY id ASC");
$stmt->execute([$quizId]);
$questions = $stmt->fetchAll();

// Handle download request
if (isset($_GET['download'])) {
    $content = "Answer Key for Quiz: " . $quiz['title'] . "\n\n";
    foreach ($questions as $index => $q) {
        $content .= ($index + 1) . ". " . $q['question_text'] . "\n";
        $content .= "Correct Answer: " . ($q['correct_option'] ?? 'Not specified') . "\n\n";
    }
    
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="answer_key_' . $quizId . '.txt"');
    header('Content-Length: ' . strlen($content));
    echo $content;
    exit;
}

// Check submission
$submitted = !empty($_POST) || isset($_SESSION['quiz_submitted_' . $quizId]);

$score = 0;
$user_answers = [];
if ($submitted) {
    if (!empty($_POST)) {
        foreach ($questions as $q) {
            $user_answer = $_POST["question_{$q['id']}"] ?? '';
            $user_answers[$q['id']] = $user_answer;
            if ($user_answer === $q['correct_option']) {
                $score++;
            }
        }
        $_SESSION['quiz_submitted_' . $quizId] = true;
        $_SESSION['user_answers_' . $quizId] = $user_answers;
        $_SESSION['quiz_score_' . $quizId] = $score;
    } else {
        $user_answers = $_SESSION['user_answers_' . $quizId] ?? [];
        $score = $_SESSION['quiz_score_' . $quizId] ?? 0;
    }
}

$view_mode = $_GET['view'] ?? 'user';
if ($view_mode !== 'user' && $view_mode !== 'key') $view_mode = 'user';

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?php echo htmlspecialchars($quiz['title']); ?> - Take Quiz</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container-fluid">
    <a class="navbar-brand" href="student_dashboard.php">QuizHut</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="student_dashboard.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container mt-5">
  <div class="card shadow-sm">
    <div class="card-body">

      <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="card-title mb-0"><?php echo htmlspecialchars($quiz['title']); ?></h3>

        <?php if ($submitted): ?>
        <div>
          <a href="?id=<?php echo $quizId; ?>&view=user" class="btn btn-outline-primary btn-sm <?php echo $view_mode === 'user' ? 'active' : ''; ?>">View My Answers</a>
          <a href="?id=<?php echo $quizId; ?>&view=key" class="btn btn-outline-primary btn-sm <?php echo $view_mode === 'key' ? 'active' : ''; ?>">View Answer Key</a>
        </div>
        <?php endif; ?>
      </div>

      <?php if (!$submitted): ?>
      <form method="POST">
        <?php foreach ($questions as $index => $q): ?>
        <div class="mb-4">
          <h6><?php echo ($index + 1) . ". " . htmlspecialchars($q['question_text']); ?></h6>
          <?php foreach (['A' => $q['option_a'], 'B' => $q['option_b'], 'C' => $q['option_c'], 'D' => $q['option_d']] as $letter => $text): ?>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="question_<?php echo $q['id']; ?>" value="<?php echo $letter; ?>" required>
            <label class="form-check-label">
              <strong><?php echo $letter; ?>.</strong> <?php echo htmlspecialchars($text); ?>
            </label>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endforeach; ?>

        <button type="submit" class="btn btn-primary">Submit Quiz</button>
        <a href="student_dashboard.php" class="btn btn-secondary">Cancel</a>
      </form>

      <?php else: ?>

      <p class="mb-4">Your Score: <?php echo $score; ?>/<?php echo count($questions); ?></p>

      <?php foreach ($questions as $index => $q): ?>
      <div class="mb-4">
        <h6><?php echo ($index + 1) . ". " . htmlspecialchars($q['question_text']); ?></h6>

        <?php if ($view_mode === 'user'): ?>
          <?php
            $options = ['A' => $q['option_a'], 'B' => $q['option_b'], 'C' => $q['option_c'], 'D' => $q['option_d']];
            $user_answer = $user_answers[$q['id']] ?? '';
            $correct = $q['correct_option'];
            foreach ($options as $letter => $text):
              $class = ($letter === $correct) ? 'text-success' :
                       (($letter === $user_answer && $letter !== $correct) ? 'text-danger' : '');
          ?>
          <div class="form-check">
            <label class="form-check-label <?php echo $class; ?>">
              <strong><?php echo $letter; ?>.</strong> <?php echo htmlspecialchars($text); ?>
            </label>
          </div>
          <?php endforeach; ?>

        <?php else: ?>
          <p>Correct Answer: <?php echo htmlspecialchars($q['correct_option']); ?></p>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>

      <a href="?id=<?php echo $quizId; ?>&download=1" class="btn btn-info">Download Answer Key</a>
      <a href="quiz.php" class="btn btn-secondary">Close</a>

      <?php endif; ?>

    </div>
  </div>
</div>

</body>
</html>
