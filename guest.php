<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 🧹 Always clear previous session for a true guest start
session_unset();
session_destroy();
session_start();

$loginError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $selected_role = $_POST['user_type'] ?? '';

    if ($username && $selected_role) {
        // Store guest info safely
        $_SESSION['user'] = [
            'id' => 0,
            'username' => $username,
            'user_type' => 'guest',
            'role' => $selected_role,
        ];

        // Redirect to corresponding dashboard based on role
        switch ($selected_role) {
            case 'teacher':
                header('Location: teacher_dashboard.php');
                break;
            case 'student':
            default:
                header('Location: student_dashboard.php');
        }
        exit;
    } else {
        $loginError = 'Please enter a username and select a role.';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Guest Login - QuizHut</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <style>
    .user-type-btn { flex:1; height:80px; display:flex; flex-direction:column; align-items:center; justify-content:center; font-size:.8rem; font-weight:bold; text-transform:uppercase; cursor:pointer; border:2px solid gray; color:black; background:white; transition:.2s; border-radius:15px; }
    .user-type-btn i { font-size:1.2rem; margin-bottom:.2rem; }
    .user-type-btn:hover { border-color:#0d6efd; color:#0d6efd; }
    .user-type-btn.active { background-color:#0d6efd; border-color:#0d6efd; color:white; }
    .user-type-container { display:flex; gap:1rem; }
    .login-card { max-width:450px; margin:0 auto; border-radius:20px; box-shadow:0 4px 15px rgba(0,0,0,.1); }
  </style>
</head>
<body class="bg-light">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
  <div class="container">
    <a class="navbar-brand text-primary fw-bold" href="home.php">QuizHut</a>
    <div>
      <?php
      if (isset($_SESSION['user']) && $_SESSION['user']['user_type'] !== 'guest') {
          echo '<span class="me-2">Hello, ' . htmlspecialchars($_SESSION['user']['username']) . '</span>';
          echo '<a class="btn btn-outline-secondary btn-sm" href="logout.php">Logout</a>';
      } else {
          echo '<a class="btn btn-link" href="guest.php">Guest</a>';
          echo '<a class="btn btn-link ms-2" href="login.php">Login</a>';
          echo '<a class="btn btn-primary ms-2" href="register.php">Sign Up</a>';
      }
      ?>
    </div>
  </div>
</nav>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-12">
      <div class="card p-4 login-card">
        <h3 class="mb-4 text-center">Guest Login</h3>

        <?php if ($loginError): ?>
          <div class="alert alert-danger"><?php echo htmlspecialchars($loginError); ?></div>
        <?php endif; ?>

        <form method="post">
          <div class="mb-4">
            <label class="form-label d-block mb-3">Select a role</label>
            <div class="user-type-container">
              <button type="button" class="user-type-btn" data-type="student"><i class="bi bi-book"></i> Student</button>
              <button type="button" class="user-type-btn" data-type="teacher"><i class="bi bi-briefcase"></i> Teacher</button>
            </div>
            <input type="hidden" name="user_type" id="user_type" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Username</label>
            <input name="username" type="text" class="form-control form-control-lg" placeholder="Enter a username" required>
          </div>

          <button type="submit" class="btn btn-primary btn-lg w-100">Enter as Guest</button>
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
