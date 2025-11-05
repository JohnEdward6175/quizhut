<?php
$dataDir = __DIR__ . '/data';
if (!is_dir($dataDir)) mkdir($dataDir, 0777, true);
$pdo = new PDO('sqlite:' . $dataDir . '/quizhut.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$pdo->exec("
    CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        first_name TEXT NOT NULL,
        last_name TEXT NOT NULL,
        email TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        user_type TEXT NOT NULL
    );
");

$users = [
    ['first_name'=>'Test','last_name'=>'Student','email'=>'test@student.com','password'=>'password123','user_type'=>'student'],
    ['first_name'=>'Test','last_name'=>'Teacher','email'=>'test@teacher.com','password'=>'password123','user_type'=>'teacher'],
    ['first_name'=>'Admin','last_name'=>'User','email'=>'admin@quizhut.com','password'=>'password123','user_type'=>'admin'],
];

foreach ($users as $u) {
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO users (first_name,last_name,email,password,user_type) VALUES (?,?,?,?,?)");
    $stmt->execute([$u['first_name'],$u['last_name'],$u['email'],password_hash($u['password'], PASSWORD_DEFAULT),$u['user_type']]);
}

echo "Test users created!";
