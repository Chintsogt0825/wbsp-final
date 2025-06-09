<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
checkRole(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = $_POST['course_id'];
    $student_id = $_POST['student_id'];

    $stmt = $pdo->prepare("DELETE FROM enrollments WHERE course_id = ? AND student_id = ?");
    $stmt->execute([$course_id, $student_id]);

    $_SESSION['success'] = "Student removed from course.";
}

redirect("view_course.php?id=$course_id");
