<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

checkRole(['teacher']);
$title = 'Add Assignment';
require_once '../includes/header.php';

if (!isset($_GET['course_id'])) {
    redirect('courses.php');
}

$course_id = $_GET['course_id'];
$teacher_id = $_SESSION['user_id'];

// Verify the course belongs to the teacher
$stmt = $pdo->prepare("SELECT id, title FROM courses WHERE id = ? AND teacher_id = ?");
$stmt->execute([$course_id, $teacher_id]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    $_SESSION['error'] = "Invalid course";
    redirect('courses.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitizeInput($_POST['title']);
    $description = sanitizeInput($_POST['description']);
    $due_date = sanitizeInput($_POST['due_date']);
    
    // Validate input
    if (empty($title) || empty($description) || empty($due_date)) {
        $error = "All fields are required";
    } else {
        $stmt = $pdo->prepare("INSERT INTO assignments (course_id, title, description, due_date) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$course_id, $title, $description, $due_date])) {
            $_SESSION['success'] = "Assignment created successfully";
            redirect("view_course.php?id=$course_id");
        } else {
            $error = "Failed to create assignment";
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4>Add Assignment to <?= $course['title'] ?></h4>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="mb-3">
                        <label for="title" class="form-label">Assignment Title</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="5" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="due_date" class="form-label">Due Date</label>
                        <input type="datetime-local" class="form-control" id="due_date" name="due_date" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Create Assignment</button>
                    <a href="view_course.php?id=<?= $course_id ?>" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set minimum date to today
    const dueDateInput = document.getElementById('due_date');
    const today = new Date().toISOString().slice(0, 16);
    dueDateInput.min = today;
});
</script>

<?php require_once '../includes/footer.php'; ?>