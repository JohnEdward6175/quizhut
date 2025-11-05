<?php
if(session_status()===PHP_SESSION_NONE) session_start();
if(!isset($_SESSION['user']) || $_SESSION['user']['user_type']!=='admin'){
    http_response_code(403); echo json_encode(['error'=>'Unauthorized']); exit;
}

$host='localhost'; $db='quizhut_db'; $user='root'; $pass=''; $charset='utf8mb4';
$dsn="mysql:host=$host;dbname=$db;charset=$charset";
$options=[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC];

try{$pdo=new PDO($dsn,$user,$pass,$options);}catch(PDOException $e){echo json_encode(['error'=>$e->getMessage()]); exit;}

$userId=$_GET['user_id'] ?? 0;

$results=[];

// Lessons
$stmt=$pdo->prepare("SELECT 'Lesson' AS type, title, filename, created_at FROM lessons WHERE author_id=?");
$stmt->execute([$userId]);
$results=array_merge($results,$stmt->fetchAll());

// Reviewers
$stmt=$pdo->prepare("SELECT 'Reviewer' AS type, title, filename, created_at FROM reviewers WHERE author_id=?");
$stmt->execute([$userId]);
$results=array_merge($results,$stmt->fetchAll());

header('Content-Type: application/json');
echo json_encode($results);
