<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

checkRole(['admin']);
$title = 'Admin Dashboard';
require_once '../includes/header.php';
?>

<div class="row">
    <div class="col-md-3">
        <div class="card text-white bg-primary mb-3">
            <div class="card-header">Users</div>
            <div class="card-body">
                <?php
                $stmt = $pdo->query("SELECT COUNT(*) FROM users");
                $count = $stmt->fetchColumn();
                ?>
                <h5 class="card-title"><?php echo $count; ?></h5>
                <p class="card-text">Total Users</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success mb-3">
            <div class="card-header">Courses</div>
            <div class="card-body">
                <?php
                $stmt = $pdo->query("SELECT COUNT(*) FROM courses");
                $count = $stmt->fetchColumn();
                ?>
                <h5 class="card-title"><?php echo $count; ?></h5>
                <p class="card-text">Total Courses</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-info mb-3">
            <div class="card-header">Teachers</div>
            <div class="card-body">
                <?php
                $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'teacher'");
                $count = $stmt->fetchColumn();
                ?>
                <h5 class="card-title"><?php echo $count; ?></h5>
                <p class="card-text">Total Teachers</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning mb-3">
            <div class="card-header">Students</div>
            <div class="card-body">
                <?php
                $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'");
                $count = $stmt->fetchColumn();
                ?>
                <h5 class="card-title"><?php echo $count; ?></h5>
                <p class="card-text">Total Students</p>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>