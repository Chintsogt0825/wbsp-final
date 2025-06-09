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

$upload_dir = '../uploads/files/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0775, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitizeInput($_POST['title']);
    $content = sanitizeInput($_POST['content']);
    $file_path = $lesson['file_name']; // Reuse existing column for general file

    if (!empty($_FILES['lesson_file']['name'])) {
        $file = $_FILES['lesson_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $new_name = uniqid('file_', true) . '.' . $ext;
        $target = $upload_dir . $new_name;

        if (move_uploaded_file($file['tmp_name'], $target)) {
            $file_path = 'uploads/files/' . $new_name;
        } else {
            $error = "Failed to upload file.";
        }
    }

    if (!isset($error)) {
        $stmt = $pdo->prepare("UPDATE lessons SET title = ?, content = ?, file_name = ? WHERE id = ?");
        if ($stmt->execute([$title, $content, $file_path, $lesson_id])) {
            $_SESSION['success'] = "Lesson updated successfully";
            redirect("view_course.php?id={$lesson['course_id']}");
        } else {
            $error = "Failed to update lesson.";
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4>Edit Lesson: <?= htmlspecialchars($lesson['title']) ?></h4>
                <p class="mb-0">Course: <?= htmlspecialchars($lesson['course_title']) ?></p>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <?php if (!empty($lesson['file_name']) && file_exists('../' . $lesson['file_name'])): ?>
                    <div class="mb-3">
                        <label class="form-label">Current File:</label><br>
                        <a href="../<?= $lesson['file_name'] ?>" target="_blank"><?= basename($lesson['file_name']) ?></a>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="title" class="form-label">Lesson Title</label>
                        <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($lesson['title']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="content" class="form-label">Content</label>
                        <textarea class="form-control" id="content" name="content" rows="10" required><?= htmlspecialchars($lesson['content']) ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="lesson_file" class="form-label">Upload Any File (PDF, Word, Video, etc.)</label>
                        <input type="file" class="form-control" id="lesson_file" name="lesson_file">
                        <small class="text-muted">Leave empty to keep the current file.</small>
                    </div>

                    <button type="submit" class="btn btn-primary">Update Lesson</button>
                    <a href="view_course.php?id=<?= $lesson['course_id'] ?>" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
