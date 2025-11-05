<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user']) || $_SESSION['user']['user_type'] !== 'student') {
    header("Location: login.php");
    exit;
}

$host = 'localhost';
$db   = 'quizhut_db';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $studentId = $_SESSION['user']['id'];
        $quizId = $_POST['quiz_id'] ?? 0;

        // Fetch quiz questions
        $stmt = $pdo->prepare("SELECT * FROM questions WHERE quiz_id = ?");
        $stmt->execute([$quizId]);
        $questions = $stmt->fetchAll();

        $totalQuestions = count($questions);
        $score = 0;

        // Insert quiz result first
        $stmt = $pdo->prepare("INSERT INTO quiz_results (quiz_id, student_id, score, total_questions, date_taken)
                               VALUES (?, ?, 0, ?, NOW())");
        $stmt->execute([$quizId, $studentId, $totalQuestions]);
        $quizResultId = $pdo->lastInsertId(); // ✅ Get the inserted quiz result ID

        // Loop questions and save each answer
        foreach ($questions as $q) {
            $qid = $q['id'];
            $selected = $_POST['q_' . $qid] ?? '';

            // Check correct answer
            $isCorrect = ($selected === strtoupper($q['correct_option'])) ? 1 : 0;
            if ($isCorrect) {
                $score++;
            }

            // Insert into quiz_answers table
            $stmt = $pdo->prepare("
                INSERT INTO quiz_answers (quiz_id, quiz_result_id, question_id, selected_option, is_correct)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$quizId, $quizResultId, $qid, $selected, $isCorrect]);
        }

        // Update final score in quiz_results
        $scorePercent = $totalQuestions ? round(($score / $totalQuestions) * 100) : 0;
        $stmt = $pdo->prepare("UPDATE quiz_results SET score = ? WHERE id = ?");
        $stmt->execute([$scorePercent, $quizResultId]);

        // Redirect to results page
        header("Location: quiz_result.php?quiz_result_id=" . $quizResultId);
        exit;
    }
} catch (PDOException $e) {
    die("Database error: " . htmlspecialchars($e->getMessage()));
}
?>
