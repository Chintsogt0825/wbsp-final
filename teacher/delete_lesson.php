<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

checkRole(['teacher']);

if (!isset($_GET['id'])) {
    redirect('courses.php');
}

$lesson_id = $_GET['id'];
$teacher_id = $_SESSION['user_id'];

// Verify the lesson belongs to the teacher
$stmt = $pdo->prepare("
    SELECT l.id, l.course_id
    FROM lessons l
    JOIN courses c ON l.course_id = c.id
    WHERE l.id = ? AND c.teacher_id = ?
");
$stmt->execute([$lesson_id, $teacher_id]);
$lesson = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$lesson) {
    $_SESSION['error'] = "Lesson not found";
    redirect('courses.php');
}

// Delete the lesson
$stmt = $pdo->prepare("DELETE FROM lessons WHERE id = ?");
if ($stmt->execute([$lesson_id])) {
    $_SESSION['success'] = "Lesson deleted successfully";
} else {
    $_SESSION['error'] = "Failed to delete lesson";
}

redirect("view_course.php?id={$lesson['course_id']}");
?>