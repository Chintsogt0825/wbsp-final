<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

checkRole(['teacher']);
$title = 'Manage Courses';
require_once '../includes/header.php';

$teacher_id = $_SESSION['user_id'];

// Handle course creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_course'])) {
    $title = sanitizeInput($_POST['title']);
    $description = sanitizeInput($_POST['description']);
    
    $stmt = $pdo->prepare("INSERT INTO courses (title, description, teacher_id) VALUES (?, ?, ?)");
    if ($stmt->execute([$title, $description, $teacher_id])) {
        $_SESSION['success'] = "Course created successfully";
        redirect('courses.php');
    } else {
        $error = "Failed to create course";
    }
}

// Handle course deletion
if (isset($_GET['delete'])) {
    $course_id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ? AND teacher_id = ?");
    if ($stmt->execute([$course_id, $teacher_id])) {
        $_SESSION['success'] = "Course deleted successfully";
    } else {
        $_SESSION['error'] = "Failed to delete course";
    }
    redirect('courses.php');
}

// Get teacher's courses
$stmt = $pdo->prepare("SELECT * FROM courses WHERE teacher_id = ?");
$stmt->execute([$teacher_id]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="row">
    <div class="col-md-12">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <h2>Your Courses</h2>
        
        <?php if (empty($courses)): ?>
            <div class="alert alert-info">You don't have any courses yet.</div>
        <?php else: ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($courses as $course): ?>
                        <tr>
                            <td><?php echo $course['id']; ?></td>
                            <td><?php echo $course['title']; ?></td>
                            <td><?php echo substr($course['description'], 0, 50) . '...'; ?></td>
                            <td>
                                <a href="view_course.php?id=<?php echo $course['id']; ?>" class="btn btn-sm btn-primary">View</a>
                                <a href="edit_course.php?id=<?php echo $course['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                <a href="courses.php?delete=<?php echo $course['id']; ?>" class="btn btn-sm btn-danger confirm-delete">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createCourseModal">Create New Course</button>
    </div>
</div>

<!-- Create Course Modal -->
<div class="modal fade" id="createCourseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Course</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="mb-3">
                        <label for="title" class="form-label">Course Title</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                    </div>
                    <button type="submit" name="create_course" class="btn btn-primary">Create Course</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>