<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

checkRole(['teacher']);
$title = 'Grade Submission';
require_once '../includes/header.php';

if (!isset($_GET['id'])) {
    redirect('assignments.php');
}

$submission_id = $_GET['id'];
$teacher_id = $_SESSION['user_id'];

// Get submission details
$stmt = $pdo->prepare("
    SELECT s.*, a.title as assignment_title, a.course_id,
           c.title as course_title, c.teacher_id,
           u.full_name as student_name
    FROM submissions s
    JOIN assignments a ON s.assignment_id = a.id
    JOIN courses c ON a.course_id = c.id
    JOIN users u ON s.student_id = u.id
    WHERE s.id = ? AND c.teacher_id = ?
");
$stmt->execute([$submission_id, $teacher_id]);
$submission = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$submission) {
    $_SESSION['error'] = "Submission not found";
    redirect('assignments.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $grade = sanitizeInput($_POST['grade']);
    $feedback = sanitizeInput($_POST['feedback']);
    
    // Validate grade
    if (!is_numeric($grade) || $grade < 0 || $grade > 100) {
        $error = "Grade must be a number between 0 and 100";
    } else {
        $stmt = $pdo->prepare("UPDATE submissions SET grade = ?, feedback = ? WHERE id = ?");
        if ($stmt->execute([$grade, $feedback, $submission_id])) {
            $_SESSION['success'] = "Submission graded successfully";
            redirect("view_assignment.php?id={$submission['assignment_id']}");
        } else {
            $error = "Failed to grade submission";
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4>Grade Submission</h4>
                <p class="mb-0">Assignment: <?= $submission['assignment_title'] ?></p>
                <p class="mb-0">Student: <?= $submission['student_name'] ?></p>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                
                <div class="mb-4">
                    <h5>Submission Details</h5>
                    <p><strong>Submitted:</strong> <?= date('M j, Y g:i A', strtotime($submission['submitted_at'])) ?></p>
                    
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
                
                <form method="POST">
                    <div class="mb-3">
                        <label for="grade" class="form-label">Grade (0-100)</label>
                        <input type="number" class="form-control" id="grade" name="grade" 
                               min="0" max="100" step="0.01" value="<?= $submission['grade'] ?? '' ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="feedback" class="form-label">Feedback</label>
                        <textarea class="form-control" id="feedback" name="feedback" rows="5"><?= $submission['feedback'] ?? '' ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Grade</button>
                    <a href="view_assignment.php?id=<?= $submission['assignment_id'] ?>" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>