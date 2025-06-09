<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

checkRole(['teacher']);
$title = 'Teacher Dashboard';
require_once '../includes/header.php';

$teacher_id = $_SESSION['user_id'];

// Get statistics
$stmt = $pdo->prepare("SELECT COUNT(*) FROM courses WHERE teacher_id = ?");
$stmt->execute([$teacher_id]);
$courses_count = $stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT e.student_id) 
    FROM enrollments e 
    JOIN courses c ON e.course_id = c.id 
    WHERE c.teacher_id = ?
");
$stmt->execute([$teacher_id]);
$students_count = $stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM assignments a 
    JOIN courses c ON a.course_id = c.id 
    WHERE c.teacher_id = ?
");
$stmt->execute([$teacher_id]);
$assignments_count = $stmt->fetchColumn();


// Get recent submissions to grade
$stmt = $pdo->prepare("
    SELECT s.id, s.submitted_at, a.title as assignment_title, 
           c.title as course_title, u.full_name as student_name
    FROM submissions s
    JOIN assignments a ON s.assignment_id = a.id
    JOIN courses c ON a.course_id = c.id
    JOIN users u ON s.student_id = u.id
    WHERE c.teacher_id = ? AND s.grade IS NULL
    ORDER BY s.submitted_at DESC
    LIMIT 5
");
$stmt->execute([$teacher_id]);
$submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Get upcoming assignments
$stmt = $pdo->prepare("
    SELECT a.id, a.title, a.due_date, c.title as course_title
    FROM assignments a
    JOIN courses c ON a.course_id = c.id
    WHERE c.teacher_id = ? AND a.due_date > NOW()
    ORDER BY a.due_date ASC
    LIMIT 5
");
$stmt->execute([$teacher_id]);
$upcoming_assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-primary sidebar collapse">
            <div class="position-sticky pt-3">
                <div class="text-center mb-4">
                    <img src="../assets/images/avatar.png" class="rounded-circle" width="80" alt="Teacher Avatar">
                    <h5 class="mt-2 text-white"><?= $_SESSION['full_name'] ?></h5>
                    <p class="text-white-50 small">Teacher</p>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active text-white" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="courses.php">
                            <i class="fas fa-book me-2"></i>My Courses
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="assignments.php">
                            <i class="fas fa-tasks me-2"></i>Assignments
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="students.php">
                            <i class="fas fa-user-graduate me-2"></i>Students
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
                <h1 class="h2">Teacher Dashboard</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-primary">Today: <?= date('M j, Y') ?></button>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">My Courses</h6>
                                    <h2 class="mb-0"><?= $courses_count ?></h2>
                                </div>
                                <i class="fas fa-book fa-3x"></i>
                            </div>
                            <a href="courses.php" class="text-white small">View All</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-info">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">Students</h6>
                                    <h2 class="mb-0"><?= $students_count ?></h2>
                                </div>
                                <i class="fas fa-user-graduate fa-3x"></i>
                            </div>
                            <a href="students.php" class="text-white small">View All</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-warning">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">Assignments</h6>
                                    <h2 class="mb-0"><?= $assignments_count ?></h2>
                                </div>
                                <i class="fas fa-tasks fa-3x"></i>
                            </div>
                            <a href="assignments.php" class="text-white small">View All</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Submissions and Upcoming Assignments -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-danger text-white">
                            <h5>Submissions to Grade</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($submissions)): ?>
                                <div class="alert alert-success">No submissions to grade!</div>
                            <?php else: ?>
                                <div class="list-group">
                                    <?php foreach ($submissions as $submission): ?>
                                        <a href="grade_submission.php?id=<?= $submission['id'] ?>" class="list-group-item list-group-item-action">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1"><?= $submission['assignment_title'] ?></h6>
                                                <small><?= time_elapsed_string($submission['submitted_at']) ?></small>
                                            </div>
                                            <p class="mb-1">Course: <?= $submission['course_title'] ?></p>
                                            <small>Student: <?= $submission['student_name'] ?></small>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <a href="grades.php" class="btn btn-sm btn-danger mt-3">View All Submissions</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5>Upcoming Assignments</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($upcoming_assignments)): ?>
                                <div class="alert alert-info">No upcoming assignments!</div>
                            <?php else: ?>
                                <div class="list-group">
                                    <?php foreach ($upcoming_assignments as $assignment): ?>
                                        <div class="list-group-item">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1"><?= $assignment['title'] ?></h6>
                                                <small><?= days_remaining($assignment['due_date']) ?> days</small>
                                            </div>
                                            <p class="mb-1">Course: <?= $assignment['course_title'] ?></p>
                                            <small>Due: <?= date('M j, Y', strtotime($assignment['due_date'])) ?></small>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <a href="assignments.php" class="btn btn-sm btn-primary mt-3">View All Assignments</a>
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
                            <?php 
                            $stmt = $pdo->prepare("SELECT * FROM courses WHERE teacher_id = ? ORDER BY created_at DESC LIMIT 3");
                            $stmt->execute([$teacher_id]);
                            $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            ?>
                            <?php if (empty($courses)): ?>
                                <div class="alert alert-warning">You don't have any courses yet.</div>
                                <a href="courses.php?action=create" class="btn btn-success">Create Your First Course</a>
                            <?php else: ?>
                                <div class="row">
                                    <?php foreach ($courses as $course): ?>
                                        <div class="col-md-4 mb-3">
                                            <div class="card h-100">
                                                <div class="card-body">
                                                    <h5 class="card-title"><?= $course['title'] ?></h5>
                                                    <p class="card-text"><?= substr($course['description'], 0, 100) . '...' ?></p>
                                                    <?php 
                                                    $stmt2 = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE course_id = ?");
                                                    $stmt2->execute([$course['id']]);
                                                    $student_count = $stmt2->fetchColumn();
                                                    ?>
                                                    <span class="badge bg-primary"><?= $student_count ?> students</span>
                                                </div>
                                                <div class="card-footer bg-white">
                                                    <a href="view_course.php?id=<?= $course['id'] ?>" class="btn btn-sm btn-primary">View Course</a>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <a href="courses.php" class="btn btn-primary mt-3">View All Courses</a>
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
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $weeks = floor($diff->d / 7);
    $diff->d -= $weeks * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );

    $values = array(
        'y' => $diff->y,
        'm' => $diff->m,
        'w' => $weeks,      // manually computed
        'd' => $diff->d,
        'h' => $diff->h,
        'i' => $diff->i,
        's' => $diff->s,
    );

    foreach ($string as $k => &$v) {
        if ($values[$k]) {
            $v = $values[$k] . ' ' . $v . ($values[$k] > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}


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