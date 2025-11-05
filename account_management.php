<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ensure the user is logged in and is an admin
if (!isset($_SESSION['user']) || $_SESSION['user']['user_type'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// MySQL connection settings
$host = 'localhost';
$db   = 'quizhut_db';
$user = 'root';
$pass = ''; // usually empty in XAMPP
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$users = [];
$dbError = '';

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // Handle inline deletion via AJAX
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user_id'])) {
        $deleteId = (int)$_POST['delete_user_id'];
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$deleteId]);
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    // Handle search and filter
    $searchQuery = $_GET['search'] ?? '';
    $filterRole = $_GET['role'] ?? 'all';

    // Build query with filters
    $sql = 'SELECT id, CONCAT(first_name, " ", last_name) AS name, email, user_type FROM users WHERE 1=1';
    $params = [];

    if (!empty($searchQuery)) {
        $sql .= ' AND (CONCAT(first_name, " ", last_name) LIKE ? OR email LIKE ?)';
        $params[] = '%' . $searchQuery . '%';
        $params[] = '%' . $searchQuery . '%';
    }

    if ($filterRole !== 'all') {
        $sql .= ' AND user_type = ?';
        $params[] = $filterRole;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $users = $stmt->fetchAll();

} catch (PDOException $e) {
    $dbError = "Cannot connect to the database: " . htmlspecialchars($e->getMessage());
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Account Management - QuizHut</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    .card, .btn-success { border-radius: 0.5rem; }
    .text-success { color: #198754 !important; }
    .btn-success { background-color: #198754 !important; border-color: #198754 !important; }
    .btn-primary { background-color: #0d6efd !important; border-color: #0d6efd !important; color: white !important; }
  </style>
</head>
<body class="bg-light">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-success shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold text-white" href="admin_dashboard.php">QuizHut</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

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

<!-- Main content -->
<div class="container py-5">
  <?php if (!empty($dbError)): ?>
    <div class="alert alert-danger text-center"><?php echo $dbError; ?></div>
  <?php else: ?>
    <div class="row justify-content-center">
      <div class="col-12">
        <div class="card p-4 mb-4 shadow-sm text-center">
          <i class="bi bi-person-lines-fill fs-1 text-success mb-2"></i>
          <h2 class="text-success fw-bold">Account Management</h2>
          <p class="lead">Manage user accounts for QuizHut.</p>
        </div>
      </div>
    </div>

    <!-- Search and Filter Form -->
    <form method="GET" class="d-flex mb-4">
      <input type="text" name="search" class="form-control me-2" placeholder="Search users..." value="<?php echo htmlspecialchars($searchQuery); ?>">
      <select name="role" class="form-select me-2" style="max-width: 150px;">
        <option value="all" <?php echo $filterRole === 'all' ? 'selected' : ''; ?>>All</option>
        <option value="student" <?php echo $filterRole === 'student' ? 'selected' : ''; ?>>Students</option>
        <option value="teacher" <?php echo $filterRole === 'teacher' ? 'selected' : ''; ?>>Teachers</option>
      </select>
      <button type="submit" class="btn btn-success">Apply</button>
    </form>

    <!-- Users List -->
    <div class="row g-4">
      <?php if (empty($users)): ?>
        <p class="text-center">No users found matching your search and filter.</p>
      <?php else: ?>
        <?php foreach ($users as $user): ?>
          <div class="col-12 col-md-6">
            <div class="card shadow-sm p-4 text-center h-100">
              <i class="bi bi-person-circle fs-1 text-success mb-2"></i>
              <h5 class="card-title"><?php echo htmlspecialchars($user['name']); ?></h5>
              <p class="card-text"><?php echo htmlspecialchars($user['email']); ?></p>
              <p class="card-text"><span class="badge bg-success"><?php echo htmlspecialchars($user['user_type']); ?></span></p>

              <!-- Delete button -->
              <button type="button" class="btn btn-danger btn-sm mb-1 delete-user-btn" data-user-id="<?php echo $user['id']; ?>">Delete</button>

              <!-- View Contributions -->
              <button type="button" class="btn btn-primary w-100 mt-2 view-contributions-btn" data-user-id="<?php echo $user['id']; ?>">
                View Contributions
              </button>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</div>

<!-- Contributions Modal -->
<div class="modal fade" id="contributionsModal" tabindex="-1" aria-labelledby="contributionsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="contributionsModalLabel">User Contributions</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="contributionsContent">Loading...</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = new bootstrap.Modal(document.getElementById('contributionsModal'));
    const contentDiv = document.getElementById('contributionsContent');

    // View contributions
    document.querySelectorAll('.view-contributions-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const userId = this.getAttribute('data-user-id');
            contentDiv.innerHTML = 'Loading...';

            fetch('fetch_contributions.php?user_id=' + userId)
                .then(response => response.text())
                .then(html => {
                    contentDiv.innerHTML = html;
                    modal.show();
                })
                .catch(err => {
                    contentDiv.innerHTML = '<p class="text-danger">Error loading contributions.</p>';
                });
        });
    });

    // Inline delete user
    document.querySelectorAll('.delete-user-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (!confirm('Are you sure you want to delete this user?')) return;

            const userId = this.getAttribute('data-user-id');
            const card = this.closest('.col-12.col-md-6');

            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ delete_user_id: userId })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    card.remove();
                } else {
                    alert('Error deleting user: ' + data.error);
                }
            })
            .catch(err => alert('Error deleting user: ' + err));
        });
    });
});
</script>
</body>
</html>
