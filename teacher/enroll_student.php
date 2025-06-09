<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

checkRole(['teacher']);  // Only teachers allowed

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('courses.php');
}

$course_id = $_POST['course_id'] ?? null;
$student_id = $_POST['student_id'] ?? null;
$teacher_id = $_SESSION['user_id'];

if (!$course_id || !$student_id) {
    $_SESSION['error'] = "Invalid data submitted.";
    redirect("view_course.php?id=$course_id");
}

// Verify the teacher owns this course
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ? AND teacher_id = ?");
$stmt->execute([$course_id, $teacher_id]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    $_SESSION['error'] = "You don't have permission to modify this course.";
    redirect('courses.php');
}

// Check if student is already enrolled
$stmt = $pdo->prepare("SELECT * FROM enrollments WHERE course_id = ? AND student_id = ?");
$stmt->execute([$course_id, $student_id]);
if ($stmt->fetch()) {
    $_SESSION['error'] = "Student is already enrolled.";
    redirect("view_course.php?id=$course_id");
}

// Enroll student
$stmt = $pdo->prepare("INSERT INTO enrollments (course_id, student_id, enrolled_at) VALUES (?, ?, NOW())");
$success = $stmt->execute([$course_id, $student_id]);

if ($success) {
    $_SESSION['success'] = "Student enrolled successfully.";
} else {
    $_SESSION['error'] = "Failed to enroll student.";
}

redirect("view_course.php?id=$course_id");
