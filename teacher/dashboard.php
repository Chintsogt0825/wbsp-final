<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

checkRole(['teacher']);
$title = 'Teacher Dashboard';
require_once '../includes/header.php';

$courses = getTeacherCourses($_SESSION['user_id']);
?>

<div class="row">
    <div class="col-md-12">
        <h2>Your Courses</h2>
        <?php if (empty($courses)): ?>
            <div class="alert alert-info">You don't have any courses yet.</div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($courses as $course): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $course['title']; ?></h5>
                                <p class="card-text"><?php echo substr($course['description'], 0, 100) . '...'; ?></p>
                                <a href="courses.php?action=view&id=<?php echo $course['id']; ?>" class="btn btn-primary">View Course</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <a href="courses.php?action=create" class="btn btn-success">Create New Course</a>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>