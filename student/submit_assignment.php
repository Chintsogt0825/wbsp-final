<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

checkRole(['student']);
$title = 'Submit Assignment';
require_once '../includes/header.php';

if (!isset($_GET['assignment_id'])) {
    redirect('dashboard.php');
}

$assignment_id = $_GET['assignment_id'];
$student_id = $_SESSION['user_id'];

// Verify the student is enrolled in the course for this assignment
$stmt = $pdo->prepare("SELECT a.* FROM assignments a JOIN enrollments e ON a.course_id = e.course_id WHERE a.id = ? AND e.student_id = ?");
$stmt->execute([$assignment_id, $student_id]);
$assignment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$assignment) {
    $_SESSION['error'] = "Invalid assignment";
    redirect('dashboard.php');
}

// Check if already submitted
$stmt = $pdo->prepare("SELECT id FROM submissions WHERE assignment_id = ? AND student_id = ?");
$stmt->execute([$assignment_id, $student_id]);
if ($stmt->fetch()) {
    $_SESSION['error'] = "You have already submitted this assignment";
    redirect("courses.php?id={$assignment['course_id']}");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submission_text = sanitizeInput($_POST['submission_text']);
    
    // Handle file upload
    $file_path = null;
    if (isset($_FILES['submission_file']) && $_FILES['submission_file']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = '../assets/uploads/';
        $file_name = uniqid() . '_' . basename($_FILES['submission_file']['name']);
        $target_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['submission_file']['tmp_name'], $target_path)) {
            $file_path = 'uploads/' . $file_name;
        } else {
            $error = "Failed to upload file";
        }
    }
    
    if (!isset($error)) {
        $stmt = $pdo->prepare("INSERT INTO submissions (assignment_id, student_id, submission_text, file_path) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$assignment_id, $student_id, $submission_text, $file_path])) {
            $_SESSION['success'] = "Assignment submitted successfully";
            redirect("courses.php?id={$assignment['course_id']}");
        } else {
            $error = "Failed to submit assignment";
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4>Submit Assignment: <?php echo $assignment['title']; ?></h4>
            </div>
            <div class="card-body">
                <p><strong>Description:</strong> <?php echo $assignment['description']; ?></p>
                <p><strong>Due Date:</strong> <?php echo date('M j, Y g:i A', strtotime($assignment['due_date'])); ?></p>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="submission_text" class="form-label">Your Submission (Text)</label>
                        <textarea class="form-control" id="submission_text" name="submission_text" rows="5" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="submission_file" class="form-label">Upload File (Optional)</label>
                        <input type="file" class="form-control" id="submission_file" name="submission_file">
                    </div>
                    <button type="submit" class="btn btn-primary">Submit Assignment</button>
                    <a href="courses.php?id=<?php echo $assignment['course_id']; ?>" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>