<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// ✅ MySQL connection
$host = 'localhost';
$db   = 'quizhut_db'; // replace with your DB name
$user = 'root';       // XAMPP default
$pass = '';           // XAMPP default
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

// Handle registration
$registerError = '';
$registerSuccess = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $password   = $_POST['password'] ?? '';
    $confirm    = $_POST['confirm_password'] ?? '';
    $user_type  = $_POST['user_type'] ?? '';
    $tos        = $_POST['tos'] ?? '';

    if (!$first_name || !$last_name || !$email || !$password || !$confirm || !$user_type || !$tos) {
        $registerError = 'Please fill in all fields and agree to the Terms.';
    } elseif ($password !== $confirm) {
        $registerError = 'Passwords do not match.';
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            $registerError = 'Email is already registered.';
        } else {
            // Hash password
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (first_name,last_name,email,password,user_type) VALUES (?,?,?,?,?)');
            $stmt->execute([$first_name, $last_name, $email, $hash, $user_type]);

            // Auto-login after registration
            $_SESSION['user'] = [
                'id' => $pdo->lastInsertId(),
                'username' => "$first_name $last_name",
                'user_type' => $user_type
            ];

            // Redirect based on role
            if ($user_type === 'student') {
                header('Location: student_dashboard.php');
            } elseif ($user_type === 'teacher') {
                header('Location: teacher_dashboard.php');
            } elseif ($user_type === 'admin') {
                header('Location: admin_dashboard.php');
            }
            exit;
        }
    }
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Register - QuizHut</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <style>
    .user-type-btn { flex:1; height:80px; display:flex; flex-direction:column; align-items:center; justify-content:center; font-size:.8rem; font-weight:bold; text-transform:uppercase; cursor:pointer; border:2px solid gray; color:black; background:white; transition:.2s; border-radius:15px; }
    .user-type-btn i { font-size:1.2rem; margin-bottom:.2rem; }
    .user-type-btn:hover { border-color:#0d6efd; color:#0d6efd; }
    .user-type-btn.active { background-color:#0d6efd; border-color:#0d6efd; color:white; }
    .user-type-container { display:flex; gap:1rem; margin-bottom:20px; }
    .register-card { max-width:450px; margin:0 auto; border-radius:20px; box-shadow:0 4px 15px rgba(0,0,0,.1); }
    ::placeholder { color:gray; }
    .tos-label a { color:#0d6efd; text-decoration:none; }
    .tos-label a:hover { text-decoration:underline; }
  </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
  <div class="container">
    <a class="navbar-brand text-primary fw-bold" href="home.php">QuizHut</a>
    <div>
      <?php if (isset($_SESSION['user'])): ?>
        <span class="me-2">Hello, <?= htmlspecialchars($_SESSION['user']['username']) ?></span>
        <a class="btn btn-outline-secondary btn-sm" href="logout.php">Logout</a>
      <?php else: ?>
        <a class="btn btn-link" href="login.php">Login</a>
        <a class="btn btn-primary ms-2" href="register.php">Sign Up</a>
      <?php endif; ?>
    </div>
  </div>
</nav>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-12">
      <div class="card p-4 register-card">
        <h3 class="mb-4 text-center">Register</h3>

        <?php if ($registerError): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($registerError) ?></div>
        <?php endif; ?>

        <form method="post">
          <div class="user-type-container">
            <button type="button" class="user-type-btn" data-type="admin"><i class="bi bi-shield-check"></i> Admin</button>
            <button type="button" class="user-type-btn" data-type="student"><i class="bi bi-book"></i> Student</button>
            <button type="button" class="user-type-btn" data-type="teacher"><i class="bi bi-briefcase"></i> Teacher</button>
          </div>
          <input type="hidden" name="user_type" id="user_type" required>

          <div class="row mb-3">
            <div class="col">
              <label class="form-label">First Name</label>
              <input name="first_name" class="form-control form-control-lg" placeholder="First name" required>
            </div>
            <div class="col">
              <label class="form-label">Last Name</label>
              <input name="last_name" class="form-control form-control-lg" placeholder="Last name" required>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Email</label>
            <input name="email" type="email" class="form-control form-control-lg" placeholder="Enter your email" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Password</label>
            <input name="password" type="password" class="form-control form-control-lg" placeholder="Enter your password" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Confirm Password</label>
            <input name="confirm_password" type="password" class="form-control form-control-lg" placeholder="Confirm your password" required>
          </div>

          <div class="mb-3 form-check">
            <input class="form-check-input" type="checkbox" id="tos" name="tos" required>
            <label class="form-check-label tos-label" for="tos">
              I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>
            </label>
          </div>

          <button type="submit" class="btn btn-primary btn-lg w-100">Create account</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
const buttons = document.querySelectorAll('.user-type-btn');
const hiddenInput = document.getElementById('user_type');
buttons.forEach(btn => {
    btn.addEventListener('click', () => {
        buttons.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        hiddenInput.value = btn.dataset.type;
    });
});
</script>

</body>
</html>
