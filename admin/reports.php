<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

checkRole(['admin']);
$title = 'System Reports';
require_once '../includes/header.php';

if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("UPDATE users SET last_activity = NOW() WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
}

$stats = [
    'total_users' => getTotalUsers(),
    'online_users' => getOnlineUsers(),
    'total_courses' => getTotalCourses(),
    'popular_courses' => getPopularCourses(),
    'user_roles' => getUserRoleDistribution()
];

function getTotalUsers() {
    global $pdo;
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    return $stmt->fetchColumn();
}

function getOnlineUsers() {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE last_activity > NOW() - INTERVAL 15 MINUTE");
    $stmt->execute();
    return $stmt->fetchColumn();
}

function getTotalCourses() {
    global $pdo;
    $stmt = $pdo->query("SELECT COUNT(*) FROM courses");
    return $stmt->fetchColumn();
}

function getPopularCourses() {
    global $pdo;
    $stmt = $pdo->query("SELECT c.title, COUNT(e.id) as enrollments
                        FROM courses c
                        LEFT JOIN enrollments e ON e.course_id = c.id
                        GROUP BY c.id
                        ORDER BY enrollments DESC
                        LIMIT 5");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getUserRoleDistribution() {
    global $pdo;
    $stmt = $pdo->query("SELECT role, COUNT(*) as count FROM users GROUP BY role");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">System Reports</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="refreshData()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                </div>
            </div>

            <!-- Real-Time Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card text-white bg-primary">
                        <div class="card-body">
                            <h5 class="card-title">Total Users</h5>
                            <h2 class="card-text"><?= number_format($stats['total_users']) ?></h2>
                            <small>Updated: <?= date('H:i:s') ?></small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-warning">
                        <div class="card-body">
                            <h5 class="card-title">Active Now</h5>
                            <h2 class="card-text"><?= number_format($stats['online_users']) ?></h2>
                            <small>15 min activity</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-info">
                        <div class="card-body">
                            <h5 class="card-title">Total Courses</h5>
                            <h2 class="card-text"><?= number_format($stats['total_courses']) ?></h2>
                            <small>Popular: <?= htmlspecialchars($stats['popular_courses'][0]['title'] ?? 'N/A') ?></small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Distribution Chart -->
            <div class="row">
                <div class="col-md-6 offset-md-3">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>User Distribution</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="usersChart" height="250"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Popular Courses Table -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Most Popular Courses</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Course</th>
                                    <th>Enrollments</th>
                                    <th>Progress</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stats['popular_courses'] as $index => $course): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= htmlspecialchars($course['title']) ?></td>
                                    <td><?= number_format($course['enrollments']) ?></td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <?php $percentage = ($course['enrollments'] / max(1, $stats['total_users'])) * 100; ?>
                                            <div class="progress-bar" role="progressbar" 
                                                 style="width: <?= $percentage ?>%" 
                                                 aria-valuenow="<?= $percentage ?>" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                                <?= round($percentage) ?>%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// User Distribution Chart
new Chart(document.getElementById('usersChart').getContext('2d'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode(array_column($stats['user_roles'], 'role')) ?>,
        datasets: [{
            data: <?= json_encode(array_column($stats['user_roles'], 'count')) ?>,
            backgroundColor: [
                'rgba(54, 162, 235, 0.7)',
                'rgba(255, 206, 86, 0.7)',
                'rgba(255, 99, 132, 0.7)',
                'rgba(75, 192, 192, 0.7)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Auto-refresh every 60 seconds
setTimeout(() => {
    window.location.reload();
}, 60000);

function refreshData() {
    window.location.reload();
}
</script>

<?php require_once '../includes/footer.php'; ?>
