<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

checkRole(['teacher']);
$title = 'Add Lesson';
require_once '../includes/header.php';

if (!isset($_GET['course_id'])) {
    redirect('courses.php');
}

$course_id = $_GET['course_id'];
$teacher_id = $_SESSION['user_id'];

// Verify the course belongs to the teacher
$stmt = $pdo->prepare("SELECT id FROM courses WHERE id = ? AND teacher_id = ?");
$stmt->execute([$course_id, $teacher_id]);
if (!$stmt->fetch()) {
    $_SESSION['error'] = "Invalid course";
    redirect('courses.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitizeInput($_POST['title']);
    $content = sanitizeInput($_POST['content']);
    $video_url = sanitizeInput($_POST['video_url']);
    
    $stmt = $pdo->prepare("INSERT INTO lessons (course_id, title, content, video_url) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$course_id, $title, $content, $video_url])) {
        $_SESSION['success'] = "Lesson added successfully";
        redirect("view_course.php?id=$course_id");
    } else {
        $error = "Failed to add lesson";
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4>Add New Lesson</h4>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="mb-3">
                        <label for="title" class="form-label">Lesson Title</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="content" class="form-label">Content</label>
                        <textarea class="form-control" id="content" name="content" rows="10" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="video_url" class="form-label">Video URL (optional)</label>
                        <input type="url" class="form-control" id="video_url" name="video_url">
                    </div>
                    <button type="submit" class="btn btn-primary">Add Lesson</button>
                    <a href="view_course.php?id=<?php echo $course_id; ?>" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>