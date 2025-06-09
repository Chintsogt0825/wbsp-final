<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

checkRole(['admin']);
$title = 'Manage Users';
require_once '../includes/header.php';

// Handle user deletion
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    if ($id != $_SESSION['user_id']) { // Prevent self-deletion
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['success'] = "User deleted successfully";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error deleting user: " . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = "You cannot delete yourself";
    }
    redirect('users.php');
}

// Get role filter if set
$role_filter = isset($_GET['role']) ? $_GET['role'] : null;
$filter_active = !empty($role_filter);

// Build SQL query based on filter
$sql = "SELECT * FROM users";
$params = [];

if ($filter_active && in_array($role_filter, ['student', 'teacher', 'admin'])) {
    $sql .= " WHERE role = ?";
    $params[] = $role_filter;
}

$sql .= " ORDER BY role, full_name";

// Get filtered users
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Manage Users</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <a href="users.php" class="btn btn-sm btn-outline-secondary <?php echo !$filter_active ? 'active' : ''; ?>">
                            All Users
                        </a>
                        <a href="users.php?role=teacher" class="btn btn-sm btn-outline-secondary <?php echo $role_filter === 'teacher' ? 'active' : ''; ?>">
                            Teachers
                        </a>
                        <a href="users.php?role=student" class="btn btn-sm btn-outline-secondary <?php echo $role_filter === 'student' ? 'active' : ''; ?>">
                            Students
                        </a>
                    </div>
                    <a href="add_user.php" class="btn btn-sm btn-outline-primary">
                        Add New User
                    </a>
                </div>
            </div>

            <?php include '../includes/alert.php'; ?>

            <div class="table-responsive">
                <table class="table table-striped table-hover" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th style="text-align:left">ID</th>
                            <th style="text-align:left">Full Name</th>
                            <th style="text-align:left">Username</th>
                            <th style="text-align:left">Email</th>
                            <th style="text-align:left">Role</th>
                            <th style="text-align:left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="6" style="text-align:center">No users found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td style="text-align:left"><?php echo htmlspecialchars($user['id']); ?></td>
                                    <td style="text-align:left"><?php echo htmlspecialchars($user['full_name']); ?></td>
                                    <td style="text-align:left"><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td style="text-align:left"><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td style="text-align:left"><?php echo ucfirst(htmlspecialchars($user['role'])); ?></td>
                                    <td style="text-align:left">
                                        <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="users.php?delete=<?php echo $user['id']; ?>" 
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
// Add confirmation for delete action
document.querySelectorAll('.confirm-delete').forEach(button => {
    button.addEventListener('click', function(e) {
        if (!confirm('Are you sure you want to delete this user?')) {
            e.preventDefault();
        }
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
