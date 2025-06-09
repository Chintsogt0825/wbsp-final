<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

checkRole(['teacher']);
$title = 'Edit Assignment';
require_once '../includes/header.php';

if (!isset($_GET['id'])) {
    redirect('assignments.php');
}

$assignment_id = $_GET['id'];
$teacher_id = $_SESSION['user_id'];

// Get assignment details
$stmt = $pdo->prepare("
    SELECT a.*, c.title as course_title 
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitizeInput($_POST['title']);
    $description = sanitizeInput($_POST['description']);
    $due_date = sanitizeInput($_POST['due_date']);
    
    $stmt = $pdo->prepare("UPDATE assignments SET title = ?, description = ?, due_date = ? WHERE id = ?");
    if ($stmt->execute([$title, $description, $due_date, $assignment_id])) {
        $_SESSION['success'] = "Assignment updated successfully";
        redirect("view_course.php?id={$assignment['course_id']}");
    } else {
        $error = "Failed to update assignment";
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4>Edit Assignment: <?= $assignment['title'] ?></h4>
                <p class="mb-0">Course: <?= $assignment['course_title'] ?></p>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="mb-3">
                        <label for="title" class="form-label">Assignment Title</label>
                        <input type="text" class="form-control" id="title" name="title" value="<?= $assignment['title'] ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="5" required><?= $assignment['description'] ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="due_date" class="form-label">Due Date</label>
                        <input type="datetime-local" class="form-control" id="due_date" name="due_date" 
                               value="<?= date('Y-m-d\TH:i', strtotime($assignment['due_date'])) ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Assignment</button>
                    <a href="view_course.php?id=<?= $assignment['course_id'] ?>" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>