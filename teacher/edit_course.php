<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

checkRole(['teacher']);
$title = 'Edit Course';
require_once '../includes/header.php';

if (!isset($_GET['id'])) {
    redirect('courses.php');
}

$course_id = $_GET['id'];
$teacher_id = $_SESSION['user_id'];

// Get course details
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ? AND teacher_id = ?");
$stmt->execute([$course_id, $teacher_id]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    $_SESSION['error'] = "Course not found";
    redirect('courses.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitizeInput($_POST['title']);
    $description = sanitizeInput($_POST['description']);
    
    $stmt = $pdo->prepare("UPDATE courses SET title = ?, description = ? WHERE id = ?");
    if ($stmt->execute([$title, $description, $course_id])) {
        $_SESSION['success'] = "Course updated successfully";
        redirect("view_course.php?id=$course_id");
    } else {
        $error = "Failed to update course";
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4>Edit Course: <?= $course['title'] ?></h4>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="mb-3">
                        <label for="title" class="form-label">Course Title</label>
                        <input type="text" class="form-control" id="title" name="title" value="<?= $course['title'] ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="5" required><?= $course['description'] ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Course</button>
                    <a href="view_course.php?id=<?= $course_id ?>" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>