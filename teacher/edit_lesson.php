<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

checkRole(['teacher']);
$title = 'Edit Lesson';
require_once '../includes/header.php';

if (!isset($_GET['id'])) {
    redirect('courses.php');
}

$lesson_id = $_GET['id'];
$teacher_id = $_SESSION['user_id'];

// Get lesson details
$stmt = $pdo->prepare("
    SELECT l.*, c.title as course_title, c.teacher_id
    FROM lessons l
    JOIN courses c ON l.course_id = c.id
    WHERE l.id = ? AND c.teacher_id = ?
");
$stmt->execute([$lesson_id, $teacher_id]);
$lesson = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$lesson) {
    $_SESSION['error'] = "Lesson not found";
    redirect('courses.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitizeInput($_POST['title']);
    $content = sanitizeInput($_POST['content']);
    $video_url = sanitizeInput($_POST['video_url']);
    
    $stmt = $pdo->prepare("UPDATE lessons SET title = ?, content = ?, video_url = ? WHERE id = ?");
    if ($stmt->execute([$title, $content, $video_url, $lesson_id])) {
        $_SESSION['success'] = "Lesson updated successfully";
        redirect("view_course.php?id={$lesson['course_id']}");
    } else {
        $error = "Failed to update lesson";
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4>Edit Lesson: <?= $lesson['title'] ?></h4>
                <p class="mb-0">Course: <?= $lesson['course_title'] ?></p>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="mb-3">
                        <label for="title" class="form-label">Lesson Title</label>
                        <input type="text" class="form-control" id="title" name="title" value="<?= $lesson['title'] ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="content" class="form-label">Content</label>
                        <textarea class="form-control" id="content" name="content" rows="10" required><?= $lesson['content'] ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="video_url" class="form-label">Video URL (optional)</label>
                        <input type="url" class="form-control" id="video_url" name="video_url" value="<?= $lesson['video_url'] ?>">
                        <small class="text-muted">YouTube or other embeddable video URL</small>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Lesson</button>
                    <a href="view_course.php?id=<?= $lesson['course_id'] ?>" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>