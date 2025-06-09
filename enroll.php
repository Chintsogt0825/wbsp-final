<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

checkRole(['student']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['course_id'])) {
    $course_id = $_POST['course_id'];
    $student_id = $_SESSION['user_id'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO enrollments (student_id, course_id) VALUES (?, ?)");
        $stmt->execute([$student_id, $course_id]);
        $_SESSION['success'] = "You have successfully enrolled in the course!";
    } catch (PDOException $e) {
        $_SESSION['error'] = "You are already enrolled in this course.";
    }
}

redirect('index.php');
?>