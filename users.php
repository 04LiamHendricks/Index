<?php
session_start();
require_once '../includes/config.php';
$user = new User();

if (!$user->isLoggedIn() || !$user->isAdmin()) {
    header("Location: ../login.php");
    exit();
}

$db = Database::getInstance();

// Handle user actions
if (isset($_GET['action'])) {
    $userID = (int)$_GET['id'];
    
    if ($_GET['action'] == 'activate') {
        $db->query("UPDATE tblUsers SET status = 'active' WHERE userID = $userID");
    } elseif ($_GET['action'] == 'suspend') {
        $db->query("UPDATE tblUsers SET status = 'suspended' WHERE userID = $userID");
    } elseif ($_GET['action'] == 'delete') {
        $db->query("DELETE FROM tblUsers WHERE userID = $userID");
    }
    header("Location: users.php");
    exit();
}

$users = $db->query("SELECT * FROM tblUsers ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Pastimes Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .admin-layout { display: flex; gap: 2rem; }
        .admin-sidebar { width: 250px; background: #1a1e2b; color: white; padding: 2rem; border-radius: 12px; min-height: 500px; }
        .admin-sidebar a { color: #a4b3c4; text-decoration: none; display: block; padding: 0.8rem 1rem; border-radius: 8px; margin-bottom: 0.5rem; }
        .admin-sidebar a:hover, .admin-sidebar a.active { background: #2c3e4e; color: white; }
        .admin-content { flex: 1; }
        .admin-table { width: 100%; background: white; border-radius: 12px; overflow: hidden; }
        .admin-table th { background: #f5f7fb; padding: 1rem; text-align: left; }
        .admin-table td { padding: 1rem; border-bottom: 1px solid #eef2f6; }
        .action-btn { padding: 0.25rem 0.75rem; border-radius: 4px; text-decoration: none; font-size: 0.8rem; margin: 0 0.25rem; }
        .btn-activate { background: #2e7d32; color: white; }
        .btn-suspend { background: #e07c4c; color: white; }
        .btn-delete { background: #d32f2f; color: white; }
    </style>
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="container">
                <div class="nav-brand"><h1><i class="fas fa-tshirt"></i> Pastimes Admin</h1></div>
                <ul class="nav-menu">
                    <li><span>Welcome, <?php echo htmlspecialchars($user->getUsername()); ?></span></li>
                    <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <main>
        <div class="container">
            <div class="admin-layout">
                <aside class="admin-sidebar">
                    <h3>Admin Menu</h3>
                    <a href="index.php"><i class="fas fa-dashboard"></i> Dashboard</a>
                    <a href="users.php" class="active"><i class="fas fa-users"></i> Users</a>
                    <a href="products.php"><i class="fas fa-tshirt"></i> Products</a>
                    <a href="orders.php"><i class="fas fa-shopping-cart"></i> Orders</a>
                    <a href="sellers.php"><i class="fas fa-tag"></i> Seller Requests</a>
                </aside>
                
                <div class="admin-content">
                    <h1>Manage Users</h1>
                    
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($u = $users->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $u['userID']; ?></td>
                                    <td><?php echo htmlspecialchars($u['fullName']); ?></td>
                                    <td><?php echo htmlspecialchars($u['username']); ?></td>
                                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                                    <td><?php echo ucfirst($u['userType']); ?></td>
                                    <td><span class="status-badge <?php echo $u['status']; ?>"><?php echo ucfirst($u['status']); ?></span></td>
                                    <td>
                                        <?php if ($u['status'] == 'pending'): ?>
                                            <a href="?action=activate&id=<?php echo $u['userID']; ?>" class="action-btn btn-activate">Activate</a>
                                        <?php endif; ?>
                                        <?php if ($u['status'] == 'active'): ?>
                                            <a href="?action=suspend&id=<?php echo $u['userID']; ?>" class="action-btn btn-suspend">Suspend</a>
                                        <?php endif; ?>
                                        <?php if ($u['status'] == 'suspended'): ?>
                                            <a href="?action=activate&id=<?php echo $u['userID']; ?>" class="action-btn btn-activate">Activate</a>
                                        <?php endif; ?>
                                        <a href="?action=delete&id=<?php echo $u['userID']; ?>" class="action-btn btn-delete" onclick="return confirm('Delete this user?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</body>
</html>