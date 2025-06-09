<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

checkRole(['teacher']);
$title = 'View Assignment';
require_once '../includes/header.php';

if (!isset($_GET['id'])) {
    redirect('assignments.php');
}

$assignment_id = $_GET['id'];
$teacher_id = $_SESSION['user_id'];

// Get assignment details
$stmt = $pdo->prepare("
    SELECT a.*, c.title as course_title, c.id as course_id
    FROM assignments a
    JOIN courses c ON a.course_id = c.id
    WHERE a.id = ? AND c.teacher_id = ?
");
$stmt->execute([$assignment_id, $teacher_id]);
$assignment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$assignment) {
    $_SESSION['error'] = "Assignment not found";
    redirect('assignments.php');
}

// Get submissions for this assignment
$stmt = $pdo->prepare("
    SELECT s.*, u.full_name as student_name
    FROM submissions s
    JOIN users u ON s.student_id = u.id
    WHERE s.assignment_id = ?
    ORDER BY s.submitted_at DESC
");
$stmt->execute([$assignment_id]);
$submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4><?= $assignment['title'] ?></h4>
                <p class="mb-0">Course: <?= $assignment['course_title'] ?></p>
            </div>
            <div class="card-body">
                <p><strong>Description:</strong></p>
                <p><?= nl2br($assignment['description']) ?></p>
                
                <div class="row mt-4">
                    <div class="col-md-6">
                        <p><strong>Due Date:</strong> <?= date('M j, Y g:i A', strtotime($assignment['due_date'])) ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Status:</strong> 
                            <?= count($submissions) ?> submission<?= count($submissions) != 1 ? 's' : '' ?>
                        </p>
                    </div>
                </div>
                
                <div class="mt-4">
                    <a href="edit_assignment.php?id=<?= $assignment_id ?>" class="btn btn-primary">Edit Assignment</a>
                    <a href="delete_assignment.php?id=<?= $assignment_id ?>" class="btn btn-danger confirm-delete">Delete Assignment</a>
                </div>
            </div>
        </div>
        
        <!-- Submissions Section -->
        <div class="card mt-4">
            <div class="card-header">
                <h5>Student Submissions</h5>
            </div>
            <div class="card-body">
                <?php if (empty($submissions)): ?>
                    <div class="alert alert-info">No submissions yet.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Submitted</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($submissions as $submission): ?>
                                    <tr>
                                        <td><?= $submission['student_name'] ?></td>
                                        <td><?= date('M j, Y g:i A', strtotime($submission['submitted_at'])) ?></td>
                                        <td>
                                            <?php if ($submission['grade'] !== null): ?>
                                                <span class="badge bg-success">Graded: <?= $submission['grade'] ?>%</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">Pending</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="grade_submission.php?id=<?= $submission['id'] ?>" class="btn btn-sm btn-primary">
                                                <?= $submission['grade'] !== null ? 'View/Edit' : 'Grade' ?>
                                            </a>
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
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5>Assignment Details</h5>
            </div>
            <div class="card-body">
                <p><strong>Course:</strong> <?= $assignment['course_title'] ?></p>
                <p><strong>Created:</strong> <?= date('M j, Y', strtotime($assignment['created_at'])) ?></p>
                
                <div class="mt-4">
                    <a href="view_course.php?id=<?= $assignment['course_id'] ?>" class="btn btn-outline-primary btn-sm">
                        View Course
                    </a>
                </div>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5>Quick Actions</h5>
            </div>
            <div class="card-body">
                <a href="add_assignment.php?course_id=<?= $assignment['course_id'] ?>" class="btn btn-success btn-sm mb-2">
                    <i class="fas fa-plus"></i> Add Another Assignment
                </a>
                <a href="view_course.php?id=<?= $assignment['course_id'] ?>" class="btn btn-primary btn-sm mb-2">
                    <i class="fas fa-book"></i> View All Course Assignments
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>