<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_GET['quiz_id'])) {
    echo "<p class='text-center text-danger'>No quiz selected.</p>";
    exit;
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=quizhut_db", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

$quiz_id = $_GET['quiz_id'];

$stmt = $pdo->prepare("
    SELECT qa.id, qa.score, qa.attempt_date, 
           u.first_name, u.last_name
    FROM quiz_attempts qa
    JOIN users u ON qa.student_id = u.id
    WHERE qa.quiz_id = :quiz_id
    ORDER BY qa.attempt_date DESC
");
$stmt->execute([':quiz_id' => $quiz_id]);
$attempts = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<?php if (!$attempts): ?>
    <p class="text-center">No attempts found for this quiz.</p>
<?php else: ?>
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th><i class="bi bi-person"></i> Student</th>
                <th><i class="bi bi-file-earmark-check"></i> Score</th>
                <th><i class="bi bi-calendar-event"></i> Date Taken</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($attempts as $row): ?>
                <tr>
                    <td><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></td>
                    <td><?php echo $row['score']; ?></td>
                    <td><?php echo $row['attempt_date']; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
