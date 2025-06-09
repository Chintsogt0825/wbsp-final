<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

checkRole(['teacher']);

if (!isset($_GET['id'])) {
    redirect('courses.php');
}

$course_id = $_GET['id'];
$teacher_id = $_SESSION['user_id'];

// Verify the course belongs to the teacher
$stmt = $pdo->prepare("SELECT id FROM courses WHERE id = ? AND teacher_id = ?");
$stmt->execute([$course_id, $teacher_id]);
if (!$stmt->fetch()) {
    $_SESSION['error'] = "Course not found";
    redirect('courses.php');
}

// Delete the course (cascade delete will handle related records)
$stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
if ($stmt->execute([$course_id])) {
    $_SESSION['success'] = "Course deleted successfully";
} else {
    $_SESSION['error'] = "Failed to delete course";
}

redirect('courses.php');
?>