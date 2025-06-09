<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

checkRole(['student']);
$title = 'My Grades';
require_once '../includes/header.php';

$student_id = $_SESSION['user_id'];

// Get all courses with grades
$stmt = $pdo->prepare("
    SELECT c.id, c.title
    FROM courses c
    JOIN enrollments e ON c.id = e.course_id
    WHERE e.student_id = ?
    ORDER BY c.title
");
$stmt->execute([$student_id]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get selected course (if any)
$selected_course = null;
if (isset($_GET['course_id'])) {
    $course_id = $_GET['course_id'];
    $stmt = $pdo->prepare("
    SELECT c.id, c.title
    FROM courses c
    JOIN enrollments e ON c.id = e.course_id
    WHERE c.id = ? AND e.student_id = ?
   ");
   $stmt->execute([$course_id, $student_id]);
   $selected_course = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($selected_course) {
        // Get all assignments for this course with submissions
        $stmt = $pdo->prepare("
            SELECT a.id, a.title, a.due_date, s.grade, s.feedback
            FROM assignments a
            LEFT JOIN submissions s ON a.id = s.assignment_id AND s.student_id = ?
            WHERE a.course_id = ?
            ORDER BY a.due_date
         ");
         $stmt->execute([$student_id, $course_id]);
         $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calculate course average
        $total = 0;
        $count = 0;
        foreach ($assignments as $assignment) {
            if ($assignment['grade'] !== null) {
                $total += $assignment['grade'];
                $count++;
            }
        }
        $course_average = $count > 0 ? round($total/$count, 1) : null;
    }
}
?>

<div class="row">
    <div class="col-md-12">
        <h2>My Grades</h2>
        
        <!-- Course Selection -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row">
                    <div class="col-md-8">
                        <select name="course_id" class="form-select" onchange="this.form.submit()">
                            <option value="">Select a course to view grades</option>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?= $course['id'] ?>" <?= isset($selected_course) && $selected_course['id'] == $course['id'] ? 'selected' : '' ?>>
                                    <?= $course['title'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>
        </div>
        
        <?php if (isset($selected_course)): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h4><?= $selected_course['title'] ?></h4>
                    <?php if ($course_average !== null): ?>
                        <p class="mb-0">Current Average: <strong><?= $course_average ?>%</strong></p>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (empty($assignments)): ?>
                        <div class="alert alert-info">This course has no assignments yet.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Assignment</th>
                                        <th>Due Date</th>
                                        <th>Status</th>
                                        <th>Grade</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($assignments as $assignment): ?>
                                        <tr>
                                            <td><?= $assignment['title'] ?></td>
                                            <td><?= date('M j, Y', strtotime($assignment['due_date'])) ?></td>
                                            <td>
                                                <?php if ($assignment['grade'] !== null): ?>
                                                    <span class="badge bg-success">Graded</span>
                                                <?php elseif ($assignment['due_date'] < date('Y-m-d H:i:s')): ?>
                                                    <span class="badge bg-danger">Overdue</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning">Pending</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($assignment['grade'] !== null): ?>
                                                    <span class="badge bg-<?= $assignment['grade'] >= 80 ? 'success' : ($assignment['grade'] >= 50 ? 'warning' : 'danger') ?>">
                                                        <?= $assignment['grade'] ?>%
                                                    </span>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($assignment['grade'] !== null): ?>
                                                    <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#feedbackModal<?= $assignment['id'] ?>">
                                                        View Feedback
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        
                                        <!-- Feedback Modal -->
                                        <?php if ($assignment['grade'] !== null): ?>
                                            <div class="modal fade" id="feedbackModal<?= $assignment['id'] ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Feedback for <?= $assignment['title'] ?></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p><strong>Grade:</strong> <?= $assignment['grade'] ?>%</p>
                                                            <?php if ($assignment['feedback']): ?>
                                                                <p><strong>Feedback:</strong></p>
                                                                <p><?= nl2br($assignment['feedback']) ?></p>
                                                            <?php else: ?>
                                                                <p>No additional feedback provided.</p>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>