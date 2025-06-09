<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

checkRole(['teacher']);
$title = 'Gradebook';
require_once '../includes/header.php';

$teacher_id = $_SESSION['user_id'];

// Get all courses taught by this teacher
$stmt = $pdo->prepare("
    SELECT id, title 
    FROM courses 
    WHERE teacher_id = ?
    ORDER BY title
");
$stmt->execute([$teacher_id]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get selected course (if any)
$selected_course = null;
if (isset($_GET['course_id'])) {
    $course_id = $_GET['course_id'];

    // Get selected course details
    $stmt = $pdo->prepare("
        SELECT id, title 
        FROM courses 
        WHERE id = ? AND teacher_id = ?
    ");
    $stmt->execute([$course_id, $teacher_id]);
    $selected_course = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($selected_course) {
        // Get all assignments for this course
        $stmt = $pdo->prepare("
            SELECT id, title, due_date
            FROM assignments
            WHERE course_id = ?
            ORDER BY due_date
        ");
        $stmt->execute([$course_id]);
        $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get all students enrolled in this course
        $stmt = $pdo->prepare("
            SELECT u.id, u.full_name
            FROM users u
            JOIN enrollments e ON u.id = e.student_id
            WHERE e.course_id = ?
            ORDER BY u.full_name
        ");
        $stmt->execute([$course_id]);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get all grades for this course
        $grades = [];
        if ($assignments && $students) {
            $stmt = $pdo->prepare("
                SELECT s.assignment_id, s.student_id, s.grade
                FROM submissions s
                JOIN assignments a ON s.assignment_id = a.id
                WHERE a.course_id = ? AND s.grade IS NOT NULL
            ");
            $stmt->execute([$course_id]);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $grades[$row['student_id']][$row['assignment_id']] = $row['grade'];
            }
        }
    }
}
?>

<div class="row">
    <div class="col-md-12">
        <h2>Gradebook</h2>
        
        <!-- Course Selection -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row">
                    <div class="col-md-8">
                        <select name="course_id" class="form-select" onchange="this.form.submit()">
                            <option value="">Select a course to view grades</option>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?= $course['id'] ?>" <?= isset($selected_course) && $selected_course['id'] == $course['id'] ? 'selected' : '' ?>>
                                    <?= $course['title'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <?php if (isset($selected_course)): ?>
                            <a href="export_grades.php?course_id=<?= $selected_course['id'] ?>" class="btn btn-success">
                                <i class="fas fa-download"></i> Export Grades
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
        
        <?php if (isset($selected_course)): ?>
            <?php if (empty($assignments) || empty($students)): ?>
                <div class="alert alert-info">
                    <?php if (empty($assignments)): ?>
                        This course has no assignments yet.
                    <?php else: ?>
                        This course has no enrolled students yet.
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>Student</th>
                                <?php foreach ($assignments as $assignment): ?>
                                    <th title="<?= $assignment['title'] ?>">
                                        <?= substr($assignment['title'], 0, 15) . (strlen($assignment['title']) > 15 ? '...' : '') ?>
                                        <br>
                                        <small><?= date('m/d', strtotime($assignment['due_date'])) ?></small>
                                    </th>
                                <?php endforeach; ?>
                                <th>Average</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td><?= $student['full_name'] ?></td>
                                    <?php 
                                    $total = 0;
                                    $count = 0;
                                    foreach ($assignments as $assignment): 
                                        $grade = $grades[$student['id']][$assignment['id']] ?? null;
                                        if ($grade !== null) {
                                            $total += $grade;
                                            $count++;
                                        }
                                    ?>
                                        <td class="<?= $grade === null ? 'bg-light' : ($grade >= 80 ? 'table-success' : ($grade >= 50 ? 'table-warning' : 'table-danger')) ?>">
                                            <?= $grade !== null ? $grade . '%' : '-' ?>
                                        </td>
                                    <?php endforeach; ?>
                                    <td class="fw-bold">
                                        <?= $count > 0 ? round($total/$count, 1) . '%' : '-' ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>