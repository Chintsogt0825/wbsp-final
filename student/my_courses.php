<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

checkRole(['student']);
$title = 'My Courses';
require_once '../includes/header.php';

$student_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT c.* FROM courses c
    JOIN enrollments e ON c.id = e.course_id
    WHERE e.student_id = ?
    ORDER BY c.title");
$stmt->execute([$student_id]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
    <h2>My Enrolled Courses</h2>
    <?php if (empty($courses)): ?>
        <div class="alert alert-warning">You are not enrolled in any courses.</div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($courses as $course): ?>
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($course['title']); ?></h5>
                            <p class="card-text"><?php echo nl2br(htmlspecialchars($course['description'])); ?></p>
                            <a href="courses.php?id=<?= $course['id'] ?>" class="btn btn-sm btn-primary">Go to Course</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
