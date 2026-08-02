<?php
session_start();
require_once '../includes/config.php';
$user = new User();

if (!$user->isLoggedIn() || !$user->isAdmin()) {
    header("Location: ../login.php");
    exit();
}

$db = Database::getInstance();

// Handle request actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $requestID = (int)$_GET['id'];
    
    if ($_GET['action'] == 'approve') {
        // Get the request details
        $req = $db->query("SELECT * FROM tblSellerRequests WHERE requestID = $requestID")->fetch_assoc();
        // Add as product
        $stmt = $db->prepare("INSERT INTO tblProducts (sellerID, productName, description, brand, condition, price, status, created_at) 
                             VALUES (?, ?, ?, ?, ?, ?, 'approved', NOW())");
        $stmt->bind_param("issssd", 
            $req['userID'],
            $req['productName'],
            $req['description'],
            $req['brand'],
            $req['condition'],
            $req['askingPrice']
        );
        $stmt->execute();
        $db->query("UPDATE tblSellerRequests SET status = 'approved' WHERE requestID = $requestID");
    } elseif ($_GET['action'] == 'reject') {
        $db->query("UPDATE tblSellerRequests SET status = 'rejected' WHERE requestID = $requestID");
    } elseif ($_GET['action'] == 'delete') {
        $db->query("DELETE FROM tblSellerRequests WHERE requestID = $requestID");
    }
    header("Location: sellers.php");
    exit();
}

$requests = $db->query("SELECT sr.*, u.username, u.fullName 
                        FROM tblSellerRequests sr 
                        JOIN tblUsers u ON sr.userID = u.userID 
                        ORDER BY sr.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Requests - Pastimes Admin</title>
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
                    <a href="orders.php"><i class="fas fa-shopping-cart"></i> Orders</a>
                    <a href="sellers.php" class="active"><i class="fas fa-tag"></i> Seller Requests</a>
                </aside>
                
                <div class="admin-content">
                    <h1>Seller Requests</h1>
                    
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Seller</th>
                                <th>Brand</th>
                                <th>Condition</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($req = $requests->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($req['productName']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($req['fullName']); ?><br><small>@<?php echo $req['username']; ?></small></td>
                                    <td><?php echo htmlspecialchars($req['brand'] ?? 'N/A'); ?></td>
                                    <td><?php echo ucfirst($req['condition']); ?></td>
                                    <td><?php echo formatPrice($req['askingPrice']); ?></td>
                                    <td><span class="status-badge <?php echo $req['status']; ?>"><?php echo ucfirst($req['status']); ?></span></td>
                                    <td>
                                        <?php if ($req['status'] == 'pending'): ?>
                                            <a href="?action=approve&id=<?php echo $req['requestID']; ?>" class="action-btn btn-approve" style="background:#2e7d32;color:white;">Approve</a>
                                            <a href="?action=reject&id=<?php echo $req['requestID']; ?>" class="action-btn btn-reject" style="background:#d32f2f;color:white;">Reject</a>
                                        <?php endif; ?>
                                        <a href="?action=delete&id=<?php echo $req['requestID']; ?>" class="action-btn btn-delete" style="background:#d32f2f;color:white;" onclick="return confirm('Delete this request?')">Delete</a>
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