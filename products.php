<?php
session_start();
require_once '../includes/config.php';
$user = new User();

if (!$user->isLoggedIn() || !$user->isAdmin()) {
    header("Location: ../login.php");
    exit();
}

$db = Database::getInstance();

// Handle product actions
if (isset($_GET['action'])) {
    $productID = (int)$_GET['id'];
    
    if ($_GET['action'] == 'approve') {
        $db->query("UPDATE tblProducts SET status = 'approved' WHERE productID = $productID");
    } elseif ($_GET['action'] == 'reject') {
        $db->query("UPDATE tblProducts SET status = 'rejected' WHERE productID = $productID");
    } elseif ($_GET['action'] == 'delete') {
        $db->query("DELETE FROM tblProducts WHERE productID = $productID");
    }
    header("Location: products.php");
    exit();
}

$products = $db->query("SELECT p.*, u.username as seller, c.categoryName, b.brandName 
                        FROM tblProducts p 
                        LEFT JOIN tblUsers u ON p.sellerID = u.userID 
                        LEFT JOIN tblCategories c ON p.categoryID = c.categoryID 
                        LEFT JOIN tblBrands b ON p.brandID = b.brandID 
                        ORDER BY p.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - Pastimes Admin</title>
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
        .btn-approve { background: #2e7d32; color: white; }
        .btn-reject { background: #e07c4c; color: white; }
        .btn-delete { background: #d32f2f; color: white; }
        .product-thumb { width: 50px; height: 50px; object-fit: cover; border-radius: 8px; }
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
                    <a href="products.php" class="active"><i class="fas fa-tshirt"></i> Products</a>
                    <a href="orders.php"><i class="fas fa-shopping-cart"></i> Orders</a>
                    <a href="sellers.php"><i class="fas fa-tag"></i> Seller Requests</a>
                </aside>
                
                <div class="admin-content">
                    <h1>Manage Products</h1>
                    
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Seller</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($p = $products->fetch_assoc()): ?>
                                <tr>
                                    <td><img src="../assets/images/<?php echo $p['imageURL'] ?: 'placeholder.jpg'; ?>" class="product-thumb"></td>
                                    <td><?php echo htmlspecialchars($p['productName']); ?></td>
                                    <td><?php echo htmlspecialchars($p['seller']); ?></td>
                                    <td><?php echo htmlspecialchars($p['categoryName'] ?? 'N/A'); ?></td>
                                    <td><?php echo formatPrice($p['price']); ?></td>
                                    <td><span class="status-badge <?php echo $p['status']; ?>"><?php echo ucfirst($p['status']); ?></span></td>
                                    <td>
                                        <?php if ($p['status'] == 'pending'): ?>
                                            <a href="?action=approve&id=<?php echo $p['productID']; ?>" class="action-btn btn-approve">Approve</a>
                                            <a href="?action=reject&id=<?php echo $p['productID']; ?>" class="action-btn btn-reject">Reject</a>
                                        <?php endif; ?>
                                        <a href="?action=delete&id=<?php echo $p['productID']; ?>" class="action-btn btn-delete" onclick="return confirm('Delete this product?')">Delete</a>
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