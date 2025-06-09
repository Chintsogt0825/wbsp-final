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

// Verify course ownership
$stmt = $pdo->prepare("SELECT id FROM courses WHERE id = ? AND teacher_id = ?");
$stmt->execute([$course_id, $teacher_id]);
if (!$stmt->fetch()) {
    $_SESSION['error'] = "Invalid course";
    redirect('courses.php');
}

// Make sure upload directory exists
$upload_dir = '../uploads/files/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0775, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitizeInput($_POST['title']);
    $content = sanitizeInput($_POST['content']);
    $file_path = null;

    if (!empty($_FILES['lesson_file']['name'])) {
        $file = $_FILES['lesson_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $new_name = uniqid('file_', true) . '.' . $ext;
        $target = $upload_dir . $new_name;

        if (move_uploaded_file($file['tmp_name'], $target)) {
            $file_path = 'uploads/files/' . $new_name;
        } else {
            $error = "File upload failed.";
        }
    }

    if (!isset($error)) {
        $stmt = $pdo->prepare("INSERT INTO lessons (course_id, title, content, video_url) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$course_id, $title, $content, $file_path])) {
            $_SESSION['success'] = "Lesson added successfully";
            redirect("view_course.php?id=$course_id");
        } else {
            $error = "Failed to add lesson";
        }
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
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="title" class="form-label">Lesson Title</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="content" class="form-label">Content</label>
                        <textarea class="form-control" id="content" name="content" rows="10" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="lesson_file" class="form-label">Upload File (PDF, Word, Excel, Video, etc.)</label>
                        <input type="file" class="form-control" id="lesson_file" name="lesson_file">
                    </div>
                    <button type="submit" class="btn btn-primary">Add Lesson</button>
                    <a href="view_course.php?id=<?= $course_id ?>" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
