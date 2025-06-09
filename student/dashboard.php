<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

checkRole(['student']);
$title = 'Student Dashboard';
require_once '../includes/header.php';

$student_id = $_SESSION['user_id'];

// Get statistics
$stmt = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE student_id = ?");
$stmt->execute([$student_id]);
$courses_count = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) 
    FROM assignments a
    JOIN enrollments e ON a.course_id = e.course_id
    WHERE e.student_id = ? AND a.due_date > NOW() AND NOT EXISTS (
        SELECT 1 FROM submissions s 
        WHERE s.assignment_id = a.id AND s.student_id = ?
    )");
$stmt->execute([$student_id, $student_id]);
$assignments_due = $stmt->fetchColumn();

// Get number of grades received
$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM submissions 
    WHERE student_id = ? AND grade IS NOT NULL
");
$stmt->execute([$student_id]);
$grades_received = $stmt->fetchColumn();

// Get upcoming assignments
$stmt = $pdo->prepare("
    SELECT a.id, a.title, a.due_date, c.title as course_title
    FROM assignments a
    JOIN enrollments e ON a.course_id = e.course_id
    JOIN courses c ON a.course_id = c.id
    WHERE e.student_id = ? AND a.due_date > NOW() AND NOT EXISTS (
        SELECT 1 FROM submissions s 
        WHERE s.assignment_id = a.id AND s.student_id = ?
    )
    ORDER BY a.due_date ASC
    LIMIT 5
");
$stmt->execute([$student_id, $student_id]);
$upcoming_assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent grades
$stmt = $pdo->prepare("
    SELECT s.id, s.grade, a.title as assignment_title, 
           c.title as course_title, s.feedback
    FROM submissions s
    JOIN assignments a ON s.assignment_id = a.id
    JOIN courses c ON a.course_id = c.id
    WHERE s.student_id = ? AND s.grade IS NOT NULL
    ORDER BY s.submitted_at DESC
    LIMIT 5
");
$stmt->execute([$student_id]);
$recent_grades = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent courses
$stmt = $pdo->prepare("
    SELECT c.id, c.title, c.description, u.full_name as teacher_name
    FROM courses c
    JOIN enrollments e ON c.id = e.course_id
    JOIN users u ON c.teacher_id = u.id
    WHERE e.student_id = ?
    ORDER BY e.enrolled_at DESC
    LIMIT 3
");
$stmt->execute([$student_id]);
$recent_courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-success sidebar collapse">
            <div class="position-sticky pt-3">
                <div class="text-center mb-4">
                    <img src="../assets/images/student.jpg" class="rounded-circle" width="80" alt="Student Avatar">
                    <h5 class="mt-2 text-white"><?= $_SESSION['full_name'] ?></h5>
                    <p class="text-white-50 small">Student</p>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active text-white" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="my_courses.php">
                            <i class="fas fa-book me-2"></i>My Courses
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="assignments.php">
                            <i class="fas fa-tasks me-2"></i>Assignments
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="grades.php">
                            <i class="fas fa-graduation-cap me-2"></i>Grades
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="profile.php">
                            <i class="fas fa-user me-2"></i>Profile
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Student Dashboard</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-success">Today: <?= date('M j, Y') ?></button>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card text-white bg-primary">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">My Courses</h6>
                                    <h2 class="mb-0"><?= $courses_count ?></h2>
                                </div>
                                <i class="fas fa-book fa-3x"></i>
                            </div>
                            <a href="my_courses.php" class="text-white small">View All</a>

                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-warning">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">Assignments Due</h6>
                                    <h2 class="mb-0"><?= $assignments_due ?></h2>
                                </div>
                                <i class="fas fa-tasks fa-3x"></i>
                            </div>
                            <a href="assignments.php" class="text-white small">View All</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-info">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">Grades Received</h6>
                                    <h2 class="mb-0"><?= $grades_received ?></h2>
                                </div>
                                <i class="fas fa-graduation-cap fa-3x"></i>
                            </div>
                            <a href="grades.php" class="text-white small">View All</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upcoming Assignments and Recent Grades -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-danger text-white">
                            <h5>Upcoming Assignments</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($upcoming_assignments)): ?>
                                <div class="alert alert-success">No upcoming assignments!</div>
                            <?php else: ?>
                                <div class="list-group">
                                    <?php foreach ($upcoming_assignments as $assignment): ?>
                                        <a href="submit_assignment.php?assignment_id=<?= $assignment['id'] ?>" class="list-group-item list-group-item-action">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1"><?= $assignment['title'] ?></h6>
                                                <small><?= days_remaining($assignment['due_date']) ?> days</small>
                                            </div>
                                            <p class="mb-1">Course: <?= $assignment['course_title'] ?></p>
                                            <small>Due: <?= date('M j, Y', strtotime($assignment['due_date'])) ?></small>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <a href="assignments.php" class="btn btn-sm btn-danger mt-3">View All Assignments</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5>Recent Grades</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($recent_grades)): ?>
                                <div class="alert alert-info">No grades received yet!</div>
                            <?php else: ?>
                                <div class="list-group">
                                    <?php foreach ($recent_grades as $grade): ?>
                                        <div class="list-group-item">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1"><?= $grade['assignment_title'] ?></h6>
                                                <span class="badge bg-<?= $grade['grade'] >= 80 ? 'success' : ($grade['grade'] >= 50 ? 'warning' : 'danger') ?>">
                                                    <?= $grade['grade'] ?>%
                                                </span>
                                            </div>
                                            <p class="mb-1">Course: <?= $grade['course_title'] ?></p>
                                            <?php if ($grade['feedback']): ?>
                                                <small>Feedback: <?= $grade['feedback'] ?></small>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <a href="grades.php" class="btn btn-sm btn-primary mt-3">View All Grades</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Courses -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5>My Recent Courses</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($recent_courses)): ?>
                                <div class="alert alert-warning">You're not enrolled in any courses yet.</div>
                                <a href="../index.php" class="btn btn-success">Browse Available Courses</a>
                            <?php else: ?>
                                <div class="row">
                                    <?php foreach ($recent_courses as $course): ?>
                                        <div class="col-md-4 mb-3">
                                            <div class="card h-100">
                                                <div class="card-body">
                                                    <h5 class="card-title"><?= $course['title'] ?></h5>
                                                    <p class="card-text"><?= substr($course['description'], 0, 100) . '...' ?></p>
                                                    <p class="card-text"><small>Teacher: <?= $course['teacher_name'] ?></small></p>
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
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Helper functions for time display -->
<?php
function days_remaining($due_date) {
    $now = new DateTime;
    $due = new DateTime($due_date);
    $interval = $now->diff($due);
    return $interval->format('%a');
}
?>

<!-- Include Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<!-- Dashboard JS -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Enable tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Sidebar toggle functionality
    document.getElementById('sidebarCollapse').addEventListener('click', function() {
        document.getElementById('sidebar').classList.toggle('active');
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
