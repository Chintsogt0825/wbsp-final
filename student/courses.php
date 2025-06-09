<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

checkRole(['student']);
$title = 'Course Details';
require_once '../includes/header.php';

if (!isset($_GET['id'])) {
    redirect('dashboard.php');
}

$course_id = $_GET['id'];
$student_id = $_SESSION['user_id'];

// Check if student is enrolled in this course
$stmt = $pdo->prepare("SELECT c.* FROM courses c JOIN enrollments e ON c.id = e.course_id WHERE c.id = ? AND e.student_id = ?");
$stmt->execute([$course_id, $student_id]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    $_SESSION['error'] = "You are not enrolled in this course";
    redirect('dashboard.php');
}

// Get teacher info
$teacher = getUserById($course['teacher_id']);

// Get lessons for this course
$stmt = $pdo->prepare("SELECT * FROM lessons WHERE course_id = ? ORDER BY created_at");
$stmt->execute([$course_id]);
$lessons = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get assignments for this course
$stmt = $pdo->prepare("SELECT a.*, s.grade FROM assignments a LEFT JOIN submissions s ON a.id = s.assignment_id AND s.student_id = ? WHERE a.course_id = ? ORDER BY a.due_date");
$stmt->execute([$student_id, $course_id]);
$assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="row">
    <div class="col-md-12">
        <h2><?php echo $course['title']; ?></h2>
        <p>Teacher: <?php echo $teacher['full_name']; ?></p>
        <p><?php echo $course['description']; ?></p>
        
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Lessons</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($lessons)): ?>
                            <p>No lessons available yet.</p>
                        <?php else: ?>
                            <ul class="list-group">
                                <?php foreach ($lessons as $lesson): ?>
                                    <li class="list-group-item">
                                        <h6><?php echo $lesson['title']; ?></h6>
                                        <?php if ($lesson['video_url']): ?>
                                            <div class="ratio ratio-16x9 my-3">
                                                <iframe src="<?php echo $lesson['video_url']; ?>" allowfullscreen></iframe>
                                            </div>
                                        <?php endif; ?>
                                        <p><?php echo $lesson['content']; ?></p>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Assignments</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($assignments)): ?>
                            <p>No assignments yet.</p>
                        <?php else: ?>
                            <ul class="list-group">
                                <?php foreach ($assignments as $assignment): ?>
                                    <li class="list-group-item">
                                        <h6><?php echo $assignment['title']; ?></h6>
                                        <p><?php echo $assignment['description']; ?></p>
                                        <small>Due: <?php echo date('M j, Y', strtotime($assignment['due_date'])); ?></small>
                                        
                                        <?php if (isset($assignment['grade'])): ?>
                                            <div class="alert alert-info mt-2">
                                                <strong>Submitted</strong>
                                                <?php if ($assignment['grade'] !== null): ?>
                                                    <br>Grade: <?php echo $assignment['grade']; ?>
                                                <?php else: ?>
                                                    <br>Awaiting grade
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <a href="submit_assignment.php?assignment_id=<?php echo $assignment['id']; ?>" class="btn btn-sm btn-primary mt-2">Submit Assignment</a>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>