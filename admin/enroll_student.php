<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
checkRole(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = $_POST['course_id'];
    $student_id = $_POST['student_id'];

    // Prevent duplicate enrollment
    $stmt = $pdo->prepare("SELECT * FROM enrollments WHERE course_id = ? AND student_id = ?");
    $stmt->execute([$course_id, $student_id]);
    if ($stmt->rowCount() === 0) {
        $stmt = $pdo->prepare("INSERT INTO enrollments (course_id, student_id) VALUES (?, ?)");
        $stmt->execute([$course_id, $student_id]);
        $_SESSION['success'] = "Student enrolled successfully.";
    } else {
        $_SESSION['error'] = "Student is already enrolled.";
    }
}

redirect("view_course.php?id=$course_id");
