<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$title = 'Home';
require_once 'includes/header.php';

// Get all courses
$stmt = $pdo->query("SELECT c.*, u.full_name as teacher_name FROM courses c JOIN users u ON c.teacher_id = u.id");
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="row">
    <div class="col-md-12">
        <h1 class="text-center mb-4">Welcome to <?php echo SITE_NAME; ?></h1>
        
        <div class="row">
            <?php foreach ($courses as $course): ?>
                <div class="col-md-4 mb-4">
                    <div class="card course-card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $course['title']; ?></h5>
                            <h6 class="card-subtitle mb-2 text-muted">By <?php echo $course['teacher_name']; ?></h6>
                            <p class="card-text"><?php echo substr($course['description'], 0, 100) . '...'; ?></p>
                            <?php if (isLoggedIn() && $_SESSION['role'] == 'student'): ?>
                                <?php
                                // Check if student is enrolled
                                $stmt = $pdo->prepare("SELECT id FROM enrollments WHERE student_id = ? AND course_id = ?");
                                $stmt->execute([$_SESSION['user_id'], $course['id']]);
                                $enrolled = $stmt->fetch();
                                ?>
                                <?php if ($enrolled): ?>
                                    <span class="badge bg-success">Enrolled</span>
                                    <a href="student/courses.php?id=<?php echo $course['id']; ?>" class="btn btn-sm btn-primary">Go to Course</a>
                                <?php else: ?>
                                    <form method="POST" action="enroll.php">
                                        <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-success">Enroll Now</button>
                                    </form>
                                <?php endif; ?>
                            <?php elseif (!isLoggedIn()): ?>
                                <a href="login.php" class="btn btn-sm btn-primary">Login to Enroll</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>