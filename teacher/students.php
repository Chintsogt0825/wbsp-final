<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

checkRole(['teacher']);
$title = 'My Students';
require_once '../includes/header.php';

$teacher_id = $_SESSION['user_id'];

// Get all students enrolled in the teacher's courses
$stmt = $pdo->prepare("
    SELECT DISTINCT u.id, u.full_name, u.email, 
           COUNT(e.course_id) as course_count
    FROM users u
    JOIN enrollments e ON u.id = e.student_id
    JOIN courses c ON e.course_id = c.id
    WHERE c.teacher_id = ? AND u.role = 'student'
    GROUP BY u.id
    ORDER BY u.full_name
");
$stmt->execute([$teacher_id]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="row">
    <div class="col-md-12">
        <h2>My Students</h2>
        
        <?php if (empty($students)): ?>
            <div class="alert alert-info">You don't have any students yet.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Email</th>
                            <th>Courses Enrolled</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?= $student['full_name'] ?></td>
                                <td><?= $student['email'] ?></td>
                                <td><?= $student['course_count'] ?></td>
                                <td>
                                    <a href="student_progress.php?student_id=<?= $student['id'] ?>" class="btn btn-sm btn-primary">
                                        View Progress
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>