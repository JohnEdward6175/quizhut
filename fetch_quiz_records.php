<?php
if (!isset($_GET['id'])) {
    echo "No Quiz Selected";
    exit;
}

$quizId = $_GET['id'];

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

    $stmt = $pdo->prepare("
        SELECT 
            qr.score, 
            qr.total_questions,
            qr.date_taken,
            u.username
        FROM quiz_results qr
        JOIN users u ON qr.student_id = u.id
        WHERE qr.quiz_id = ?
        ORDER BY qr.date_taken DESC
    ");
    $stmt->execute([$quizId]);
    $records = $stmt->fetchAll();

    if (!$records) {
        echo '<div class="text-center py-3 text-muted">No one has taken this quiz yet.</div>';
        exit;
    }

    echo '<table class="table table-striped text-center mb-0">';
    echo '<thead><tr><th>Username</th><th>Score (%)</th><th>Out of</th><th>Date Taken</th></tr></thead><tbody>';

    foreach ($records as $r) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($r['username']) . '</td>';
        echo '<td>' . $r['score'] . '</td>';
        echo '<td>' . $r['total_questions'] . '</td>';
        echo '<td>' . htmlspecialchars($r['date_taken']) . '</td>';
        echo '</tr>';
    }

    echo '</tbody></table>';

} catch (PDOException $e) {
    echo '<div class="alert alert-danger text-center">Database error: ' . htmlspecialchars($e->getMessage()) . '</div>';
}
