<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

checkRole(['student']);
$title = 'View Submission';
require_once '../includes/header.php';

if (!isset($_GET['id'])) {
    redirect('assignments.php');
}

$submission_id = $_GET['id'];
$student_id = $_SESSION['user_id'];

// Get submission details
$stmt = $pdo->prepare("
    SELECT s.*, a.title AS assignment_title, a.due_date,
           c.title AS course_title, c.id AS course_id
    FROM submissions s
    JOIN assignments a ON s.assignment_id = a.id
    JOIN courses c ON a.course_id = c.id
    WHERE s.id = ? AND s.student_id = ?
");
$stmt->execute([$submission_id, $student_id]);
$submission = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$submission) {
    $_SESSION['error'] = "Submission not found";
    redirect('assignments.php');
}

// Check if assignment is late
$is_late = strtotime($submission['submitted_at']) > strtotime($submission['due_date']);
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4>Submission Details</h4>
                <p class="mb-0">Assignment: <?= $submission['assignment_title'] ?></p>
                <p class="mb-0">Course: <?= $submission['course_title'] ?></p>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <p><strong>Submitted:</strong> <?= date('M j, Y g:i A', strtotime($submission['submitted_at'])) ?></p>
                    <?php if ($is_late): ?>
                        <p class="text-danger"><strong>This submission was late.</strong></p>
                    <?php endif; ?>
                    
                    <?php if ($submission['submission_text']): ?>
                        <div class="mb-3">
                            <label class="form-label"><strong>Submission Text:</strong></label>
                            <div class="border p-3 bg-light"><?= nl2br($submission['submission_text']) ?></div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($submission['file_path']): ?>
                        <div class="mb-3">
                            <label class="form-label"><strong>Submitted File:</strong></label>
                            <div class="border p-3 bg-light">
                                <a href="../assets/<?= $submission['file_path'] ?>" target="_blank" class="btn btn-sm btn-primary">
                                    <i class="fas fa-download"></i> Download File
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if ($submission['grade'] !== null): ?>
                    <div class="alert alert-<?= $submission['grade'] >= 80 ? 'success' : ($submission['grade'] >= 50 ? 'warning' : 'danger') ?>">
                        <h5>Grade: <?= $submission['grade'] ?>%</h5>
                        <?php if ($submission['feedback']): ?>
                            <p class="mb-0"><strong>Feedback:</strong> <?= nl2br($submission['feedback']) ?></p>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        This submission is awaiting grading by your instructor.
                    </div>
                <?php endif; ?>
                
                <div class="mt-4">
                    <a href="assignments.php" class="btn btn-primary">Back to Assignments</a>
                    <a href="courses.php?id=<?= $submission['course_id'] ?>" class="btn btn-secondary">View Course</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>