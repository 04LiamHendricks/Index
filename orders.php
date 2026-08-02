<?php
session_start();
require_once '../includes/config.php';
$user = new User();

if (!$user->isLoggedIn() || !$user->isAdmin()) {
    header("Location: ../login.php");
    exit();
}

$db = Database::getInstance();

// Handle order actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $orderID = (int)$_GET['id'];
    $status = $_GET['action'];
    $allowed = ['processing', 'shipped', 'delivered', 'cancelled'];
    if (in_array($status, $allowed)) {
        $db->query("UPDATE tblOrders SET status = '$status' WHERE orderID = $orderID");
    }
    header("Location: orders.php");
    exit();
}

$orders = $db->query("SELECT o.*, u.username, u.fullName 
                      FROM tblOrders o 
                      JOIN tblUsers u ON o.userID = u.userID 
                      ORDER BY o.orderDate DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - Pastimes Admin</title>
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
        .action-btn { padding: 0.25rem 0.75rem; border-radius: 4px; text-decoration: none; font-size: 0.8rem; margin: 0 0.15rem; }
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
                    <a href="users.php"><i class="fas fa-users"></i> Users</a>
                    <a href="products.php"><i class="fas fa-tshirt"></i> Products</a>
                    <a href="orders.php" class="active"><i class="fas fa-shopping-cart"></i> Orders</a>
                    <a href="sellers.php"><i class="fas fa-tag"></i> Seller Requests</a>
                </aside>
                
                <div class="admin-content">
                    <h1>Manage Orders</h1>
                    
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Order Ref</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Payment</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($order = $orders->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?php echo $order['orderRef']; ?></strong></td>
                                    <td><?php echo htmlspecialchars($order['fullName']); ?><br><small>@<?php echo $order['username']; ?></small></td>
                                    <td><?php echo formatPrice($order['totalAmount']); ?></td>
                                    <td><span class="status-badge <?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                                    <td><span class="status-badge <?php echo $order['paymentStatus']; ?>"><?php echo ucfirst($order['paymentStatus']); ?></span></td>
                                    <td><?php echo date('d M Y', strtotime($order['orderDate'])); ?></td>
                                    <td>
                                        <?php if ($order['status'] == 'pending'): ?>
                                            <a href="?action=processing&id=<?php echo $order['orderID']; ?>" class="action-btn btn-approve" style="background:#2196f3;color:white;">Process</a>
                                        <?php endif; ?>
                                        <?php if ($order['status'] == 'processing'): ?>
                                            <a href="?action=shipped&id=<?php echo $order['orderID']; ?>" class="action-btn btn-approve" style="background:#2e7d32;color:white;">Ship</a>
                                        <?php endif; ?>
                                        <?php if ($order['status'] == 'shipped'): ?>
                                            <a href="?action=delivered&id=<?php echo $order['orderID']; ?>" class="action-btn btn-approve" style="background:#1e2f3c;color:white;">Deliver</a>
                                        <?php endif; ?>
                                        <?php if (!in_array($order['status'], ['delivered', 'cancelled'])): ?>
                                            <a href="?action=cancelled&id=<?php echo $order['orderID']; ?>" class="action-btn btn-delete" style="background:#d32f2f;color:white;" onclick="return confirm('Cancel this order?')">Cancel</a>
                                        <?php endif; ?>
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