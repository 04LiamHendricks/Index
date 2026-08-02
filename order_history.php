<?php
// ============================================
// File: order_history.php - Order History
// ============================================
session_start();
if(!isset($_SESSION['user'])){
    header("Location: login.php");
    exit();
}
include("includes/DBConn.php");

$username = $_SESSION['user'];
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM tblUser WHERE username='$username'"));
$userID = $user['userID'];

// Get orders
$orders = mysqli_query($conn, "SELECT o.*, 
    (SELECT COUNT(*) FROM tblOrderItems WHERE orderID = o.orderID) as item_count
    FROM tblOrders o 
    WHERE o.userID = $userID 
    ORDER BY o.created_at DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Order History | Pastimes</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f5f7fb; padding: 2rem; }
        .container { max-width: 1000px; margin: 0 auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem; }
        .header h1 { font-weight: 800; }
        .order-card {
            background: white;
            border-radius: 20px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border: 1px solid #eef2f6;
        }
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        .order-ref { font-weight: 700; font-size: 1.1rem; }
        .order-date { color: #7a8e9e; font-size: 0.9rem; }
        .order-status {
            padding: 0.3rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.8rem;
        }
        .status-pending { background: #fef3c7; color: #b45309; }
        .status-processing { background: #dbeafe; color: #1e40af; }
        .status-shipped { background: #dcfce7; color: #2e7d32; }
        .status-delivered { background: #dcfce7; color: #2e7d32; }
        .status-cancelled { background: #fee2e2; color: #b91c1c; }
        .order-total { font-size: 1.2rem; font-weight: 800; color: #e07c4c; }
        .order-items { margin-top: 1rem; border-top: 1px solid #eef2f6; padding-top: 1rem; }
        .order-item { display: flex; justify-content: space-between; padding: 0.3rem 0; font-size: 0.9rem; }
        .order-summary { display: flex; justify-content: space-between; font-weight: 600; border-top: 2px solid #eef2f6; padding-top: 0.5rem; margin-top: 0.5rem; }
        .back-link { color: #e07c4c; text-decoration: none; }
        .empty { text-align: center; padding: 4rem; color: #7a8e9e; }
        .empty i { font-size: 3rem; margin-bottom: 1rem; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1><i class="fas fa-history"></i> Order History</h1>
        <div>
            <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> Continue Shopping</a>
            <a href="profile.php" style="margin-left:1rem;color:#7a8e9e;text-decoration:none;"><i class="fas fa-user"></i> Profile</a>
        </div>
    </div>

    <?php if(mysqli_num_rows($orders) == 0): ?>
        <div class="empty">
            <i class="fas fa-shopping-bag"></i>
            <h3>No orders yet</h3>
            <p>Start shopping and your orders will appear here.</p>
            <a href="index.php" class="back-link" style="display:inline-block;margin-top:1rem;">Start Shopping →</a>
        </div>
    <?php else: 
        $grand_total = 0;
        while($order = mysqli_fetch_assoc($orders)): 
            $grand_total += $order['total_amount'];
            $items = mysqli_query($conn, "SELECT oi.*, c.name, c.image FROM tblOrderItems oi 
                                          JOIN tblClothes c ON oi.itemID = c.itemID 
                                          WHERE oi.orderID = {$order['orderID']}");
    ?>
        <div class="order-card">
            <div class="order-header">
                <div>
                    <div class="order-ref">📦 <?php echo htmlspecialchars($order['order_ref']); ?></div>
                    <div class="order-date"><i class="far fa-calendar-alt"></i> <?php echo date('d M Y, H:i', strtotime($order['created_at'])); ?></div>
                </div>
                <div>
                    <span class="order-status status-<?php echo strtolower($order['status']); ?>">
                        <?php echo $order['status']; ?>
                    </span>
                    <span class="order-total">R <?php echo number_format($order['total_amount'], 2); ?></span>
                </div>
            </div>
            <div class="order-items">
                <?php while($item = mysqli_fetch_assoc($items)): ?>
                    <div class="order-item">
                        <span><?php echo htmlspecialchars($item['name']); ?> × <?php echo $item['quantity']; ?></span>
                        <span>R <?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                    </div>
                <?php endwhile; ?>
                <div class="order-summary">
                    <span>Total</span>
                    <span>R <?php echo number_format($order['total_amount'], 2); ?></span>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
    
    <!-- Grand Total -->
    <div style="background:white;border-radius:20px;padding:1.5rem;text-align:right;border:1px solid #eef2f6;">
        <span style="color:#7a8e9e;">Total spent: </span>
        <span style="font-size:1.5rem;font-weight:800;color:#e07c4c;">R <?php echo number_format($grand_total, 2); ?></span>
    </div>
    <?php endif; ?>
</div>
</body>
</html>