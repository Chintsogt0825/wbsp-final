<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

checkRole(['admin']);
$title = 'Admin Dashboard';
require_once '../includes/header.php';

// Get statistics
$users_count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$teachers_count = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'teacher'")->fetchColumn();
$students_count = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
$courses_count = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();

// Get recent activities
$activities = $pdo->query("
    SELECT 'course' as type, title as name, created_at as date, id FROM courses 
    UNION 
    SELECT 'user' as type, full_name as name, created_at as date, id FROM users 
    ORDER BY date DESC LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
            <div class="position-sticky pt-3">
                <div class="text-center mb-4">
                    <img src="../assets/images/admin.jpg" class="rounded-circle" width="80" alt="Admin Avatar">
                    <h5 class="mt-2 text-white"><?= $_SESSION['full_name'] ?></h5>
                    <p class="text-white-50 small">Admin</p>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active text-white" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="users.php">
                            <i class="fas fa-users me-2"></i>Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="courses.php">
                            <i class="fas fa-book me-2"></i>Courses
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="reports.php">
                            <i class="fas fa-chart-bar me-2"></i>Reports
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="settings.php">
                            <i class="fas fa-cog me-2"></i>Settings
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Dashboard</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary">Export</button>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle">
                        <span data-feather="calendar"></span>
                        This week
                    </button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-white bg-primary">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">Total Users</h6>
                                    <h2 class="mb-0"><?= $users_count ?></h2>
                                </div>
                                <i class="fas fa-users fa-3x"></i>
                            </div>
                            <a href="users.php" class="text-white small">View All</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">Teachers</h6>
                                    <h2 class="mb-0"><?= $teachers_count ?></h2>
                                </div>
                                <i class="fas fa-chalkboard-teacher fa-3x"></i>
                            </div>
                            <a href="users.php?role=teacher" class="text-white small">View All</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-info">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">Students</h6>
                                    <h2 class="mb-0"><?= $students_count ?></h2>
                                </div>
                                <i class="fas fa-user-graduate fa-3x"></i>
                            </div>
                            <a href="users.php?role=student" class="text-white small">View All</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-warning">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">Courses</h6>
                                    <h2 class="mb-0"><?= $courses_count ?></h2>
                                </div>
                                <i class="fas fa-book fa-3x"></i>
                            </div>
                            <a href="courses.php" class="text-white small">View All</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity and Quick Actions -->
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5>Recent Activity</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Type</th>
                                            <th>Name</th>
                                            <th>Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($activities as $activity): ?>
                                            <tr>
                                                <td><?= ucfirst($activity['type']) ?></td>
                                                <td><?= $activity['name'] ?></td>
                                                <td><?= date('M j, Y', strtotime($activity['date'])) ?></td>
                                                <td>
                                                    <a href="<?= $activity['type'] == 'user' ? 'users.php?action=view&id=' : 'courses.php?action=view&id=' ?><?= $activity['id'] ?>" class="btn btn-sm btn-primary">View</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="users.php?action=add" class="btn btn-primary mb-2">
                                    <i class="fas fa-user-plus me-2"></i>Add New User
                                </a>
                                <a href="courses.php?action=add" class="btn btn-success mb-2">
                                    <i class="fas fa-book-medical me-2"></i>Create Course
                                </a>
                                <a href="reports.php" class="btn btn-info mb-2">
                                    <i class="fas fa-chart-pie me-2"></i>View Reports
                                </a>
                                <a href="settings.php" class="btn btn-warning">
                                    <i class="fas fa-cogs me-2"></i>System Settings
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Include Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<!-- Dashboard JS -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Enable tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Sidebar toggle functionality
    document.getElementById('sidebarCollapse').addEventListener('click', function() {
        document.getElementById('sidebar').classList.toggle('active');
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>