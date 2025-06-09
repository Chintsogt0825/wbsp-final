<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

checkRole(['teacher']);  // Teacher access

$title = 'View Course';
require_once '../includes/header.php';

if (!isset($_GET['id'])) {
    redirect('courses.php');
}

$course_id = $_GET['id'];
$teacher_id = $_SESSION['user_id'];

// Get course details to verify teacher owns this course
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ? AND teacher_id = ?");
$stmt->execute([$course_id, $teacher_id]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    $_SESSION['error'] = "Course not found or you don't have permission";
    redirect('courses.php');
}

// Get lessons
$stmt = $pdo->prepare("SELECT * FROM lessons WHERE course_id = ? ORDER BY created_at");
$stmt->execute([$course_id]);
$lessons = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get assignments
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
        <h2><?= htmlspecialchars($course['title']); ?></h2>
        <p><?= htmlspecialchars($course['description']); ?></p>

        <!-- Lessons -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>Lessons</h5>
                        <a href="add_lesson.php?course_id=<?= $course_id ?>" class="btn btn-sm btn-success">Add Lesson</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($lessons)): ?>
                            <p>No lessons added yet.</p>
                        <?php else: ?>
                            <ul class="list-group">
                                <?php foreach ($lessons as $lesson): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <?= htmlspecialchars($lesson['title']); ?>
                                        <div>
                                            <a href="edit_lesson.php?id=<?= $lesson['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                            <a href="delete_lesson.php?id=<?= $lesson['id'] ?>" class="btn btn-sm btn-danger confirm-delete">Delete</a>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Assignments -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>Assignments</h5>
                        <a href="add_assignment.php?course_id=<?= $course_id ?>" class="btn btn-sm btn-success">Add Assignment</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($assignments)): ?>
                            <p>No assignments added yet.</p>
                        <?php else: ?>
                            <ul class="list-group">
                                <?php foreach ($assignments as $assignment): ?>
                                    <li class="list-group-item">
                                        <h6><?= htmlspecialchars($assignment['title']); ?></h6>
                                        <p><?= htmlspecialchars(substr($assignment['description'], 0, 50)) . '...'; ?></p>
                                        <small>Due: <?= date('M j, Y', strtotime($assignment['due_date'])); ?></small>
                                        <div class="mt-2">
                                            <a href="view_assignment.php?id=<?= $assignment['id'] ?>" class="btn btn-sm btn-primary">View</a>
                                            <a href="edit_assignment.php?id=<?= $assignment['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                            <a href="delete_assignment.php?id=<?= $assignment['id'] ?>" class="btn btn-sm btn-danger confirm-delete">Delete</a>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enrolled Students -->
        <div class="card mt-4">
            <div class="card-header">
                <h5>Enrolled Students (<?= count($students); ?>)</h5>
            </div>
            <div class="card-body">

                <!-- Add Student Form -->
                <form action="enroll_student.php" method="POST" class="mb-3 d-flex">
                    <input type="hidden" name="course_id" value="<?= $course_id ?>">
                    <select name="student_id" class="form-control me-2" required>
                        <option value="">-- Select Student --</option>
                        <?php
                        $stmt = $pdo->prepare("SELECT id, full_name FROM users WHERE role = 'student' AND id NOT IN (
                            SELECT student_id FROM enrollments WHERE course_id = ?
                        )");
                        $stmt->execute([$course_id]);
                        $available_students = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        foreach ($available_students as $student) {
                            echo "<option value=\"{$student['id']}\">" . htmlspecialchars($student['full_name']) . "</option>";
                        }
                        ?>
                    </select>
                    <button type="submit" class="btn btn-success">Enroll</button>
                </form>

                <!-- Student List -->
                <?php if (empty($students)): ?>
                    <p>No students enrolled yet.</p>
                <?php else: ?>
                    <ul class="list-group">
                        <?php foreach ($students as $student): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?= htmlspecialchars($student['full_name']); ?>
                                <form action="remove_student.php" method="POST" onsubmit="return confirm('Remove this student?');" style="margin:0;">
                                    <input type="hidden" name="student_id" value="<?= $student['id']; ?>">
                                    <input type="hidden" name="course_id" value="<?= $course_id; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Remove</button>
                                </form>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
