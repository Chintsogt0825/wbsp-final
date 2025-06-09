<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

checkRole(['admin']);
$title = 'Manage Courses';
require_once '../includes/header.php';
// Handle course deletion
if (isset($_GET['delete'])) {
    $course_id = (int)$_GET['delete'];

    try {
        // You can add more checks if needed, e.g., prevent deleting courses that have students enrolled
        $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
        $stmt->execute([$course_id]);
        $_SESSION['success'] = "Course deleted successfully";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error deleting course: " . $e->getMessage();
    }
    redirect('course.php');
}
// Fetch all courses with teacher name
$sql = "SELECT c.id, c.title, c.description, u.full_name AS teacher_name 
        FROM courses c
        JOIN users u ON c.teacher_id = u.id
        ORDER BY c.title";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Manage Courses</h1>
                <a href="add_course.php" class="btn btn-sm btn-outline-primary">
                    Add New Course
                </a>
            </div>

            <?php include '../includes/alert.php'; ?>

            <div class="table-responsive">
                <table class="table table-striped table-hover" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th style="text-align:left">ID</th>
                            <th style="text-align:left">Course Title</th>
                            <th style="text-align:left">Teacher</th>
                            <th style="text-align:left">Description</th>
                            <th style="text-align:left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($courses)): ?>
                            <tr>
                                <td colspan="5" style="text-align:center">No courses found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($courses as $course): ?>
                                <tr>
                                    <td style="text-align:left"><?php echo htmlspecialchars($course['id']); ?></td>
                                    <td style="text-align:left"><?php echo htmlspecialchars($course['title']); ?></td>
                                    <td style="text-align:left"><?php echo htmlspecialchars($course['teacher_name']); ?></td>
                                    <td style="text-align:left"><?php echo htmlspecialchars($course['description']); ?></td>
                                    <td style="text-align:left">
                                        <a href="view_course.php?id=<?php echo $course['id']; ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <a href="edit_course.php?id=<?php echo $course['id']; ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="course.php?delete=<?php echo $course['id']; ?>" 
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
