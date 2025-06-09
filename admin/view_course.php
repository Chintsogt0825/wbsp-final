<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

checkRole(['admin']);  // only admin can access

$title = 'View Course - Admin';
require_once '../includes/header.php';

if (!isset($_GET['id'])) {
    redirect('courses.php');
}

$course_id = $_GET['id'];

// Admin can view any course
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->execute([$course_id]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    $_SESSION['error'] = "Course not found";
    redirect('courses.php');
}

// Get lessons for this course
$stmt = $pdo->prepare("SELECT * FROM lessons WHERE course_id = ? ORDER BY created_at");
$stmt->execute([$course_id]);
$lessons = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get assignments for this course
$stmt = $pdo->prepare("SELECT * FROM assignments WHERE course_id = ? ORDER BY due_date");
$stmt->execute([$course_id]);
$assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get enrolled students
$stmt = $pdo->prepare("SELECT u.id, u.full_name FROM users u JOIN enrollments e ON u.id = e.student_id WHERE e.course_id = ?");
$stmt->execute([$course_id]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="row">
    <div class="col-md-12">
        <h2><?php echo htmlspecialchars($course['title']); ?></h2>
        <p><?php echo htmlspecialchars($course['description']); ?></p>
        
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>Lessons</h5>
                        <a href="add_lesson.php?course_id=<?php echo $course_id; ?>" class="btn btn-sm btn-success">Add Lesson</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($lessons)): ?>
                            <p>No lessons added yet.</p>
                        <?php else: ?>
                            <ul class="list-group">
                                <?php foreach ($lessons as $lesson): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <?php echo htmlspecialchars($lesson['title']); ?>
                                        <div>
                                            <a href="edit_lesson.php?id=<?php echo $lesson['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                            <a href="delete_lesson.php?id=<?php echo $lesson['id']; ?>" class="btn btn-sm btn-danger confirm-delete">Delete</a>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>Assignments</h5>
                        <a href="add_assignment.php?course_id=<?php echo $course_id; ?>" class="btn btn-sm btn-success">Add Assignment</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($assignments)): ?>
                            <p>No assignments added yet.</p>
                        <?php else: ?>
                            <ul class="list-group">
                                <?php foreach ($assignments as $assignment): ?>
                                    <li class="list-group-item">
                                        <h6><?php echo htmlspecialchars($assignment['title']); ?></h6>
                                        <p><?php echo htmlspecialchars(substr($assignment['description'], 0, 50)) . '...'; ?></p>
                                        <small>Due: <?php echo date('M j, Y', strtotime($assignment['due_date'])); ?></small>
                                        <div class="mt-2">
                                            <a href="view_assignment.php?id=<?php echo $assignment['id']; ?>" class="btn btn-sm btn-primary">View</a>
                                            <a href="edit_assignment.php?id=<?php echo $assignment['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                            <a href="delete_assignment.php?id=<?php echo $assignment['id']; ?>" class="btn btn-sm btn-danger confirm-delete">Delete</a>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5>Enrolled Students (<?php echo count($students); ?>)</h5>
            </div>
            <div class="card-body">
                <?php if (empty($students)): ?>
                    <p>No students enrolled yet.</p>
                <?php else: ?>
                    <ul class="list-group">
                        <?php foreach ($students as $student): ?>
                            <li class="list-group-item"><?php echo htmlspecialchars($student['full_name']); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
