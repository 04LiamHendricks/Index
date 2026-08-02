<?php
session_start();
require_once '../includes/config.php';
$user = new User();

if (!$user->isLoggedIn() || !$user->isAdmin()) {
    header("Location: ../login.php");
    exit();
}

$db = Database::getInstance();

// Get stats
$totalUsers = $db->query("SELECT COUNT(*) as count FROM tblUsers")->fetch_assoc()['count'];
$totalProducts = $db->query("SELECT COUNT(*) as count FROM tblProducts WHERE status = 'approved'")->fetch_assoc()['count'];
$totalOrders = $db->query("SELECT COUNT(*) as count FROM tblOrders")->fetch_assoc()['count'];
$totalRevenue = $db->query("SELECT SUM(totalAmount) as total FROM tblOrders WHERE paymentStatus = 'paid'")->fetch_assoc()['total'] ?? 0;
$pendingRequests = $db->query("SELECT COUNT(*) as count FROM tblSellerRequests WHERE status = 'pending'")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Pastimes</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .admin-layout {
            display: flex;
            gap: 2rem;
        }
        .admin-sidebar {
            width: 250px;
            background: #1a1e2b;
            color: white;
            padding: 2rem;
            border-radius: 12px;
            min-height: 500px;
        }
        .admin-sidebar a {
            color: #a4b3c4;
            text-decoration: none;
            display: block;
            padding: 0.8rem 1rem;
            border-radius: 8px;
            margin-bottom: 0.5rem;
            transition: 0.2s;
        }
        .admin-sidebar a:hover, .admin-sidebar a.active {
            background: #2c3e4e;
            color: white;
        }
        .admin-content {
            flex: 1;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            text-align: center;
        }
        .stat-card h3 {
            font-size: 2rem;
            color: #e07c4c;
        }
        .stat-card p {
            color: #7a8e9e;
            margin-top: 0.5rem;
        }
        .recent-activity {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="container">
                <div class="nav-brand">
                    <h1><i class="fas fa-tshirt"></i> Pastimes Admin</h1>
                </div>
                <ul class="nav-menu">
                    <li><span>Welcome, <?php echo htmlspecialchars($user->getUsername()); ?></span></li>
                    <li><a href="../index.php"><i class="fas fa-store"></i> Store</a></li>
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
                    <a href="index.php" class="active"><i class="fas fa-dashboard"></i> Dashboard</a>
                    <a href="users.php"><i class="fas fa-users"></i> Users</a>
                    <a href="products.php"><i class="fas fa-tshirt"></i> Products</a>
                    <a href="orders.php"><i class="fas fa-shopping-cart"></i> Orders</a>
                    <a href="sellers.php"><i class="fas fa-tag"></i> Seller Requests</a>
                </aside>
                
                <div class="admin-content">
                    <h1>Dashboard</h1>
                    
                    <div class="stats-grid">
                        <div class="stat-card">
                            <h3><?php echo $totalUsers; ?></h3>
                            <p><i class="fas fa-users"></i> Users</p>
                        </div>
                        <div class="stat-card">
                            <h3><?php echo $totalProducts; ?></h3>
                            <p><i class="fas fa-tshirt"></i> Products</p>
                        </div>
                        <div class="stat-card">
                            <h3><?php echo $totalOrders; ?></h3>
                            <p><i class="fas fa-shopping-cart"></i> Orders</p>
                        </div>
                        <div class="stat-card">
                            <h3><?php echo formatPrice($totalRevenue); ?></h3>
                            <p><i class="fas fa-money-bill-wave"></i> Revenue</p>
                        </div>
                        <div class="stat-card">
                            <h3><?php echo $pendingRequests; ?></h3>
                            <p><i class="fas fa-clock"></i> Pending Requests</p>
                        </div>
                    </div>
                    
                    <div class="recent-activity">
                        <h2>Recent Activity</h2>
                        <?php
                        $recentOrders = $db->query("SELECT * FROM tblOrders ORDER BY orderDate DESC LIMIT 5");
                        if ($recentOrders->num_rows > 0): ?>
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Order Ref</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($order = $recentOrders->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $order['orderRef']; ?></td>
                                            <td><?php echo formatPrice($order['totalAmount']); ?></td>
                                            <td><span class="status-badge <?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                                            <td><?php echo date('d M Y', strtotime($order['orderDate'])); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p>No recent orders</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>