<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

checkRole(['admin']);
$title = 'Manage Courses';
require_once '../includes/header.php';

// Handle course creation for admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_course'])) {
    $title = sanitizeInput($_POST['title']);
    $description = sanitizeInput($_POST['description']);
    $teacher_id = $_POST['teacher_id'] ?? null;

    if (!$teacher_id) {
        $_SESSION['error'] = "Please select a teacher.";
        redirect('courses.php');
    }

    $stmt = $pdo->prepare("INSERT INTO courses (title, description, teacher_id) VALUES (?, ?, ?)");
    if ($stmt->execute([$title, $description, $teacher_id])) {
        $_SESSION['success'] = "Course created successfully";
        redirect('courses.php');
    } else {
        $_SESSION['error'] = "Failed to create course";
    }
}

// Handle course deletion
if (isset($_GET['delete'])) {
    $course_id = (int)$_GET['delete'];

    try {
        $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
        $stmt->execute([$course_id]);
        $_SESSION['success'] = "Course deleted successfully";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error deleting course: " . $e->getMessage();
    }
    redirect('courses.php');
}

// Fetch all courses with teacher name
$sql = "SELECT c.id, c.title, c.description, u.full_name AS teacher_name 
        FROM courses c
        JOIN users u ON c.teacher_id = u.id
        ORDER BY c.title";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all teachers for dropdown
$stmt = $pdo->prepare("SELECT id, full_name FROM users WHERE role = 'teacher' ORDER BY full_name");
$stmt->execute();
$teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Manage Courses</h1>
                <!-- Button trigger modal -->
                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#createCourseModal">
                    Add New Course
                </button>
            </div>

            <?php include '../includes/alert.php'; ?>

            <div class="table-responsive">
                <table class="table table-striped table-hover" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Course Title</th>
                            <th>Teacher</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($courses)): ?>
                            <tr>
                                <td colspan="5" class="text-center">No courses found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($courses as $course): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($course['id']); ?></td>
                                    <td><?php echo htmlspecialchars($course['title']); ?></td>
                                    <td><?php echo htmlspecialchars($course['teacher_name']); ?></td>
                                    <td><?php echo htmlspecialchars($course['description']); ?></td>
                                    <td>
                                        <a href="view_course.php?id=<?php echo $course['id']; ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <a href="edit_course.php?id=<?php echo $course['id']; ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="courses.php?delete=<?php echo $course['id']; ?>" 
                                           class="btn btn-sm btn-danger confirm-delete">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>

<!-- Create Course Modal -->
<div class="modal fade" id="createCourseModal" tabindex="-1" aria-labelledby="createCourseModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createCourseModalLabel">Create New Course</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                <?php endif; ?>
                
                <form method="POST" action="courses.php">
                    <div class="mb-3">
                        <label for="title" class="form-label">Course Title</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="teacher_id" class="form-label">Assign Teacher</label>
                        <select name="teacher_id" id="teacher_id" class="form-select" required>
                            <option value="">Select a teacher</option>
                            <?php foreach ($teachers as $teacher): ?>
                                <option value="<?php echo $teacher['id']; ?>"><?php echo htmlspecialchars($teacher['full_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" name="create_course" class="btn btn-primary">Create Course</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Confirmation on delete
document.querySelectorAll('.confirm-delete').forEach(button => {
    button.addEventListener('click', function(e) {
        if (!confirm('Are you sure you want to delete this course?')) {
            e.preventDefault();
        }
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
