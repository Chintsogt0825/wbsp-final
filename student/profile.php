<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

checkRole(['student']);
$title = 'My Profile';
require_once '../includes/header.php';

$student_id = $_SESSION['user_id'];

// Get student details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitizeInput($_POST['full_name']);
    $email = sanitizeInput($_POST['email']);
    $password = !empty($_POST['password']) ? sanitizeInput($_POST['password']) : null;
    
    // Validate input
    if (empty($full_name) || empty($email)) {
        $error = "Full name and email are required";
    } else {
        // Check if email exists for another user
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $student_id]);
        if ($stmt->fetch()) {
            $error = "Email already exists for another user";
        } else {
            // Update profile
            if ($password) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, password = ? WHERE id = ?");
                $result = $stmt->execute([$full_name, $email, $hashed_password, $student_id]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ? WHERE id = ?");
                $result = $stmt->execute([$full_name, $email, $student_id]);
            }
            
            if ($result) {
                $_SESSION['success'] = "Profile updated successfully";
                $_SESSION['full_name'] = $full_name;
                redirect('profile.php');
            } else {
                $error = "Failed to update profile";
            }
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4>My Profile</h4>
            </div>
            <div class="card-body">
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" value="<?= $student['username'] ?>" disabled>
                        </div>
                        <div class="col-md-6">
                            <label for="role" class="form-label">Role</label>
                            <input type="text" class="form-control" id="role" value="<?= ucfirst($student['role']) ?>" disabled>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" value="<?= $student['full_name'] ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= $student['email'] ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">New Password (leave blank to keep current)</label>
                        <input type="password" class="form-control" id="password" name="password">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Update Profile</button>
                </form>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5>My Learning Statistics</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <?php
                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE student_id = ?");
                        $stmt->execute([$student_id]);
                        $course_count = $stmt->fetchColumn();
                        ?>
                        <div class="text-center">
                            <h3><?= $course_count ?></h3>
                            <p class="text-muted">Enrolled Courses</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <?php
                        $stmt = $pdo->prepare("
                           SELECT COUNT(*) 
                           FROM assignments a
                           JOIN enrollments e ON a.course_id = e.course_id
                           WHERE e.student_id = ?
                        ");
                        $stmt->execute([$student_id]);
                        $assignment_count = $stmt->fetchColumn();
                        ?>
                        <div class="text-center">
                            <h3><?= $assignment_count ?></h3>
                            <p class="text-muted">Total Assignments</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <?php
                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM submissions WHERE student_id = ? AND grade IS NOT NULL");
                        $stmt->execute([$student_id]);
                        $submission_count = $stmt->fetchColumn();
                        ?>
                        <div class="text-center">
                            <h3><?= $submission_count ?></h3>
                            <p class="text-muted">Graded Assignments</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>