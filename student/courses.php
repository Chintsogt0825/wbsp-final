<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

checkRole(['student']);
$title = 'All Courses';
require_once '../includes/header.php';

// Fetch all courses from DB
$stmt = $pdo->query("SELECT c.*, u.full_name AS teacher_name FROM courses c JOIN users u ON c.teacher_id = u.id ORDER BY c.created_at DESC");
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="row">
    <div class="col-md-12">
        <h2>All Courses</h2>
        <?php if (empty($courses)): ?>
            <div class="alert alert-warning">No courses available at the moment.</div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($courses as $course): ?>
                    <div class="col-md-4 mb-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($course['title']) ?></h5>
                                <p class="card-text"><?= htmlspecialchars(substr($course['description'], 0, 100)) ?>...</p>
                                <p class="card-text"><small>Teacher: <?= htmlspecialchars($course['teacher_name']) ?></small></p>
                            </div>
                            <div class="card-footer bg-white">
                                <a href="courses.php?id=<?= $course['id'] ?>" class="btn btn-sm btn-primary">Go to Course</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
