<?php
// ============================================
// File: seller_requests.php - Admin Seller Requests
// ============================================
session_start();
include("includes/DBConn.php");

if(!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: admin_login.php");
    exit();
}

// Handle actions
if(isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if($_GET['action'] == 'approve') {
        // Get request details
        $req = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM tblSellerRequests WHERE requestID=$id"));
        if($req) {
            // Add to clothes table
            $query = "INSERT INTO tblClothes (name, description, brand, price, categoryID, condition, size, image, status, quantity) 
                      VALUES ('{$req['name']}', '{$req['description']}', '{$req['brand']}', {$req['price']}, 
                              (SELECT categoryID FROM tblCategories WHERE categoryName='{$req['category']}' LIMIT 1), 
                              '{$req['condition']}', '{$req['size']}', '{$req['image']}', 'Available', 1)";
            mysqli_query($conn, $query);
            // Update request status
            mysqli_query($conn, "UPDATE tblSellerRequests SET status='Approved' WHERE requestID=$id");
            $_SESSION['admin_msg'] = "Seller request approved and product added!";
        }
    } elseif($_GET['action'] == 'reject') {
        mysqli_query($conn, "UPDATE tblSellerRequests SET status='Rejected' WHERE requestID=$id");
        $_SESSION['admin_msg'] = "Seller request rejected.";
    }
    header("Location: seller_requests.php");
    exit();
}

$requests = mysqli_query($conn, "SELECT r.*, u.name as user_name, u.email as user_email 
                                 FROM tblSellerRequests r 
                                 JOIN tblUser u ON r.userID = u.userID 
                                 ORDER BY r.created_at DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Seller Requests | Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f1f5f9; padding: 2rem; }
        .container { max-width: 1200px; margin: 0 auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem; }
        .header h1 { font-weight: 800; }
        .header a { color: #e07c4c; text-decoration: none; }
        .card {
            background: white;
            border-radius: 20px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid #eef2f6;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .request-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        .request-user { font-weight: 700; }
        .request-date { color: #7a8e9e; font-size: 0.9rem; }
        .status-badge {
            padding: 0.3rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.8rem;
        }
        .status-pending { background: #fef3c7; color: #b45309; }
        .status-approved { background: #dcfce7; color: #2e7d32; }
        .status-rejected { background: #fee2e2; color: #b91c1c; }
        .request-details { display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem 2rem; margin: 1rem 0; }
        .request-details .label { color: #7a8e9e; font-size: 0.8rem; }
        .request-details .value { font-weight: 500; }
        .actions { display: flex; gap: 0.5rem; margin-top: 1rem; flex-wrap: wrap; }
        .btn {
            padding: 0.4rem 1.2rem;
            border-radius: 30px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.85rem;
        }
        .btn-success { background: #2e7d32; color: white; }
        .btn-danger { background: #d9534f; color: white; }
        .btn-secondary { background: #eef2f6; color: #1e2f3c; }
        .btn-sm { padding: 0.2rem 0.8rem; font-size: 0.75rem; }
        .msg { background: #dcfce7; color: #166534; padding: 1rem; border-radius: 16px; margin-bottom: 1.5rem; }
        .empty { text-align: center; padding: 4rem; color: #7a8e9e; }
        .product-img { width: 80px; height: 80px; object-fit: cover; border-radius: 12px; background: #f0f3f8; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1><i class="fas fa-store"></i> Seller Requests</h1>
        <a href="admin_dashboard.php">← Back to Dashboard</a>
    </div>
    
    <?php if(isset($_SESSION['admin_msg'])): ?>
        <div class="msg">✅ <?php echo htmlspecialchars($_SESSION['admin_msg']); unset($_SESSION['admin_msg']); ?></div>
    <?php endif; ?>
    
    <?php if(mysqli_num_rows($requests) == 0): ?>
        <div class="empty">
            <i class="fas fa-box-open" style="font-size:3rem;display:block;margin-bottom:1rem;"></i>
            No seller requests yet.
        </div>
    <?php else: ?>
        <?php while($req = mysqli_fetch_assoc($requests)): ?>
            <div class="card">
                <div class="request-header">
                    <div>
                        <span class="request-user"><i class="fas fa-user"></i> <?php echo htmlspecialchars($req['user_name']); ?></span>
                        <span style="color:#7a8e9e;margin-left:1rem;font-size:0.9rem;">
                            <?php echo htmlspecialchars($req['user_email']); ?>
                        </span>
                        <span class="request-date" style="display:block;font-size:0.8rem;margin-top:0.2rem;">
                            <?php echo date('d M Y, H:i', strtotime($req['created_at'])); ?>
                        </span>
                    </div>
                    <span class="status-badge status-<?php echo strtolower($req['status']); ?>">
                        <?php echo $req['status']; ?>
                    </span>
                </div>
                
                <div style="display:flex;gap:1.5rem;flex-wrap:wrap;">
                    <?php if($req['image'] && file_exists("images/".$req['image'])): ?>
                        <img src="images/<?php echo htmlspecialchars($req['image']); ?>" class="product-img">
                    <?php else: ?>
                        <div class="product-img" style="display:flex;align-items:center;justify-content:center;font-size:2rem;color:#a0aec0;">
                            <i class="fas fa-tshirt"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div style="flex:1;">
                        <div style="font-weight:700;font-size:1.2rem;"><?php echo htmlspecialchars($req['name']); ?></div>
                        <?php if($req['brand']): ?>
                            <div style="color:#7a8e9e;font-size:0.9rem;"><i class="fas fa-tag"></i> <?php echo htmlspecialchars($req['brand']); ?></div>
                        <?php endif; ?>
                        <div style="margin:0.5rem 0;"><?php echo htmlspecialchars($req['description']); ?></div>
                        <div style="display:flex;gap:2rem;flex-wrap:wrap;font-size:0.9rem;">
                            <div><span style="color:#7a8e9e;">Price:</span> <strong>R <?php echo number_format($req['price'], 2); ?></strong></div>
                            <div><span style="color:#7a8e9e;">Category:</span> <?php echo htmlspecialchars($req['category']); ?></div>
                            <div><span style="color:#7a8e9e;">Condition:</span> <?php echo htmlspecialchars($req['condition']); ?></div>
                            <div><span style="color:#7a8e9e;">Size:</span> <?php echo htmlspecialchars($req['size']); ?></div>
                            <div><span style="color:#7a8e9e;">Contact:</span> <?php echo htmlspecialchars($req['contact']); ?></div>
                        </div>
                    </div>
                </div>
                
                <?php if($req['status'] == 'Pending'): ?>
                    <div class="actions">
                        <a href="?action=approve&id=<?php echo $req['requestID']; ?>" class="btn btn-success btn-sm"><i class="fas fa-check"></i> Approve & Add</a>
                        <a href="?action=reject&id=<?php echo $req['requestID']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Reject this request?')"><i class="fas fa-times"></i> Reject</a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>
</div>
</body>
</html>