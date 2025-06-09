<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

checkRole(['student']);
$title = 'My Assignments';
require_once '../includes/header.php';

$student_id = $_SESSION['user_id'];

// Get all assignments for the student's enrolled courses
$stmt = $pdo->prepare("
    SELECT a.id, a.title, a.due_date, c.title AS course_title, 
           s.id AS submission_id, s.grade, s.submitted_at
    FROM assignments a
    JOIN enrollments e ON a.course_id = e.course_id
    JOIN courses c ON a.course_id = c.id
    LEFT JOIN submissions s ON a.id = s.assignment_id AND s.student_id = ?
    WHERE e.student_id = ?
    ORDER BY a.due_date ASC
");
$stmt->execute([$student_id, $student_id]);
$assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Categorize assignments
$pending = [];
$submitted = [];
$overdue = [];

foreach ($assignments as $assignment) {
    $due_date = new DateTime($assignment['due_date']);
    $now = new DateTime();
    
    if ($assignment['submission_id']) {
        $submitted[] = $assignment;
    } elseif ($due_date < $now) {
        $overdue[] = $assignment;
    } else {
        $pending[] = $assignment;
    }
}
?>

<div class="row">
    <div class="col-md-12">
        <h2>My Assignments</h2>
        
        <!-- Pending Assignments -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5>Pending Assignments</h5>
            </div>
            <div class="card-body">
                <?php if (empty($pending)): ?>
                    <div class="alert alert-success">No pending assignments!</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Assignment</th>
                                    <th>Course</th>
                                    <th>Due Date</th>
                                    <th>Days Left</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pending as $assignment): ?>
                                    <?php
                                    $due_date = new DateTime($assignment['due_date']);
                                    $now = new DateTime();
                                    $interval = $now->diff($due_date);
                                    $days_left = $interval->format('%a');
                                    ?>
                                    <tr>
                                        <td><?= $assignment['title'] ?></td>
                                        <td><?= $assignment['course_title'] ?></td>
                                        <td><?= date('M j, Y g:i A', strtotime($assignment['due_date'])) ?></td>
                                        <td><?= $days_left ?></td>
                                        <td>
                                            <a href="submit_assignment.php?assignment_id=<?= $assignment['id'] ?>" class="btn btn-sm btn-primary">Submit</a>
                                            <a href="courses.php?id=<?= $assignment['course_id'] ?>" class="btn btn-sm btn-secondary">View Course</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Submitted Assignments -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5>Submitted Assignments</h5>
            </div>
            <div class="card-body">
                <?php if (empty($submitted)): ?>
                    <div class="alert alert-info">No submitted assignments yet.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Assignment</th>
                                    <th>Course</th>
                                    <th>Submitted On</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($submitted as $assignment): ?>
                                    <tr>
                                        <td><?= $assignment['title'] ?></td>
                                        <td><?= $assignment['course_title'] ?></td>
                                        <td><?= date('M j, Y g:i A', strtotime($assignment['submitted_at'])) ?></td>
                                        <td>
                                            <?php if ($assignment['grade'] !== null): ?>
                                                <span class="badge bg-success">Graded: <?= $assignment['grade'] ?>%</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">Pending Review</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="view_submission.php?id=<?= $assignment['submission_id'] ?>" class="btn btn-sm btn-primary">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Overdue Assignments -->
        <div class="card mb-4">
            <div class="card-header bg-danger text-white">
                <h5>Overdue Assignments</h5>
            </div>
            <div class="card-body">
                <?php if (empty($overdue)): ?>
                    <div class="alert alert-success">No overdue assignments!</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Assignment</th>
                                    <th>Course</th>
                                    <th>Due Date</th>
                                    <th>Days Overdue</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($overdue as $assignment): ?>
                                    <?php
                                    $due_date = new DateTime($assignment['due_date']);
                                    $now = new DateTime();
                                    $interval = $now->diff($due_date);
                                    $days_overdue = $interval->format('%a');
                                    ?>
                                    <tr>
                                        <td><?= $assignment['title'] ?></td>
                                        <td><?= $assignment['course_title'] ?></td>
                                        <td><?= date('M j, Y g:i A', strtotime($assignment['due_date'])) ?></td>
                                        <td><?= $days_overdue ?></td>
                                        <td>
                                            <?php if (!$assignment['submission_id']): ?>
                                                <a href="submit_assignment.php?assignment_id=<?= $assignment['id'] ?>" class="btn btn-sm btn-danger">Submit Late</a>
                                            <?php else: ?>
                                                <a href="view_submission.php?id=<?= $assignment['submission_id'] ?>" class="btn btn-sm btn-primary">View Submission</a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>