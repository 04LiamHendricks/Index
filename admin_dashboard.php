<?php
// ============================================
// File: admin_dashboard.php - Admin Dashboard
// Full CRUD for users and products
// ============================================
session_start();
include("includes/DBConn.php");

// Check admin access
if(!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    if(isset($_SESSION['user'])) {
        header("Location: index.php");
    } else {
        header("Location: admin_login.php");
    }
    exit();
}

// Handle POST actions
if(isset($_POST['action'])) {
    $action = $_POST['action'];
    
    // === USER MANAGEMENT ===
    if($action == 'add_user') {
        $name = sanitize($_POST['name']);
        $email = sanitize($_POST['email']);
        $username = sanitize($_POST['username']);
        $password = md5($_POST['password']);
        $role = sanitize($_POST['role']);
        $status = sanitize($_POST['status']);
        $phone = sanitize($_POST['phone'] ?? '');
        $address = sanitize($_POST['address'] ?? '');
        
        $query = "INSERT INTO tblUser (name, email, username, password, role, status, phone, address) 
                  VALUES ('$name', '$email', '$username', '$password', '$role', '$status', '$phone', '$address')";
        mysqli_query($conn, $query);
        $_SESSION['admin_msg'] = "User added successfully!";
    }
    
    if($action == 'update_user') {
        $id = (int)$_POST['userID'];
        $name = sanitize($_POST['name']);
        $email = sanitize($_POST['email']);
        $username = sanitize($_POST['username']);
        $role = sanitize($_POST['role']);
        $status = sanitize($_POST['status']);
        $phone = sanitize($_POST['phone'] ?? '');
        $address = sanitize($_POST['address'] ?? '');
        
        $query = "UPDATE tblUser SET 
                  name='$name', email='$email', username='$username', 
                  role='$role', status='$status', phone='$phone', address='$address' 
                  WHERE userID=$id";
        mysqli_query($conn, $query);
        $_SESSION['admin_msg'] = "User updated successfully!";
    }
    
    if($action == 'delete_user') {
        $id = (int)$_POST['userID'];
        mysqli_query($conn, "DELETE FROM tblUser WHERE userID=$id");
        $_SESSION['admin_msg'] = "User deleted successfully!";
    }
    
    if($action == 'approve_user') {
        $id = (int)$_POST['userID'];
        mysqli_query($conn, "UPDATE tblUser SET status='Approved' WHERE userID=$id");
        $_SESSION['admin_msg'] = "User approved successfully!";
    }
    
    // === CLOTHING MANAGEMENT ===
    if($action == 'add_clothing') {
        $name = sanitize($_POST['name']);
        $description = sanitize($_POST['description']);
        $brand = sanitize($_POST['brand'] ?? '');
        $price = (float)$_POST['price'];
        $original_price = isset($_POST['original_price']) ? (float)$_POST['original_price'] : $price;
        $quantity = (int)$_POST['quantity'];
        $categoryID = (int)$_POST['categoryID'];
        $status = sanitize($_POST['status']);
        $condition = sanitize($_POST['condition'] ?? 'Good');
        $size = sanitize($_POST['size'] ?? 'M');
        
        // Handle image upload
        $image = '';
        if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $target_dir = "images/";
            if(!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            $image = time() . '_' . basename($_FILES['image']['name']);
            $target_file = $target_dir . $image;
            move_uploaded_file($_FILES['image']['tmp_name'], $target_file);
        }
        
        $query = "INSERT INTO tblClothes (name, description, brand, price, original_price, quantity, categoryID, status, condition, size, image) 
                  VALUES ('$name', '$description', '$brand', $price, $original_price, $quantity, $categoryID, '$status', '$condition', '$size', '$image')";
        mysqli_query($conn, $query);
        $_SESSION['admin_msg'] = "Product added successfully!";
    }
    
    if($action == 'update_clothing') {
        $id = (int)$_POST['itemID'];
        $name = sanitize($_POST['name']);
        $description = sanitize($_POST['description']);
        $brand = sanitize($_POST['brand'] ?? '');
        $price = (float)$_POST['price'];
        $original_price = isset($_POST['original_price']) ? (float)$_POST['original_price'] : $price;
        $quantity = (int)$_POST['quantity'];
        $categoryID = (int)$_POST['categoryID'];
        $status = sanitize($_POST['status']);
        $condition = sanitize($_POST['condition'] ?? 'Good');
        $size = sanitize($_POST['size'] ?? 'M');
        
        // Handle image upload
        $image_sql = '';
        if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $target_dir = "images/";
            if(!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            $image = time() . '_' . basename($_FILES['image']['name']);
            $target_file = $target_dir . $image;
            move_uploaded_file($_FILES['image']['tmp_name'], $target_file);
            $image_sql = ", image='$image'";
        }
        
        $query = "UPDATE tblClothes SET 
                  name='$name', description='$description', brand='$brand', 
                  price=$price, original_price=$original_price, quantity=$quantity, 
                  categoryID=$categoryID, status='$status', condition='$condition', size='$size'
                  $image_sql
                  WHERE itemID=$id";
        mysqli_query($conn, $query);
        $_SESSION['admin_msg'] = "Product updated successfully!";
    }
    
    if($action == 'delete_clothing') {
        $id = (int)$_POST['itemID'];
        // Get image to delete
        $img = mysqli_fetch_assoc(mysqli_query($conn, "SELECT image FROM tblClothes WHERE itemID=$id"));
        if($img && $img['image'] && file_exists("images/".$img['image'])) {
            unlink("images/".$img['image']);
        }
        mysqli_query($conn, "DELETE FROM tblClothes WHERE itemID=$id");
        $_SESSION['admin_msg'] = "Product deleted successfully!";
    }
    
    header("Location: admin_dashboard.php");
    exit();
}

// Handle GET actions
if(isset($_GET['action'])) {
    if($_GET['action'] == 'approve' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        mysqli_query($conn, "UPDATE tblUser SET status='Approved' WHERE userID=$id");
        $_SESSION['admin_msg'] = "User approved!";
        header("Location: admin_dashboard.php");
        exit();
    }
    if($_GET['action'] == 'delete_user' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        mysqli_query($conn, "DELETE FROM tblUser WHERE userID=$id");
        $_SESSION['admin_msg'] = "User deleted!";
        header("Location: admin_dashboard.php");
        exit();
    }
    if($_GET['action'] == 'delete_product' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $img = mysqli_fetch_assoc(mysqli_query($conn, "SELECT image FROM tblClothes WHERE itemID=$id"));
        if($img && $img['image'] && file_exists("images/".$img['image'])) {
            unlink("images/".$img['image']);
        }
        mysqli_query($conn, "DELETE FROM tblClothes WHERE itemID=$id");
        $_SESSION['admin_msg'] = "Product deleted!";
        header("Location: admin_dashboard.php");
        exit();
    }
}

// Fetch data
$users = mysqli_query($conn, "SELECT * FROM tblUser ORDER BY userID DESC");
$clothes = mysqli_query($conn, "SELECT c.*, cat.categoryName FROM tblClothes c LEFT JOIN tblCategories cat ON c.categoryID = cat.categoryID ORDER BY c.itemID DESC");
$categories = mysqli_query($conn, "SELECT * FROM tblCategories");
$pending_users = mysqli_query($conn, "SELECT * FROM tblUser WHERE status='Pending'");

// Get stats
$stats = [
    'total_users' => mysqli_num_rows(mysqli_query($conn, "SELECT * FROM tblUser")),
    'total_products' => mysqli_num_rows(mysqli_query($conn, "SELECT * FROM tblClothes")),
    'pending_users' => mysqli_num_rows($pending_users),
    'total_orders' => mysqli_num_rows(mysqli_query($conn, "SELECT * FROM tblOrders"))
];

$msg = isset($_SESSION['admin_msg']) ? $_SESSION['admin_msg'] : '';
unset($_SESSION['admin_msg']);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard | Pastimes</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #f1f5f9;
            color: #1a1e2b;
        }
        .admin-header {
            background: #1e2f3c;
            color: white;
            padding: 1.5rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .admin-header h1 { font-weight: 800; font-size: 1.8rem; }
        .admin-header a { color: #a4b3c4; text-decoration: none; margin-left: 1rem; }
        .admin-header a:hover { color: white; }
        .container { max-width: 1400px; margin: 0 auto; padding: 2rem; }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border: 1px solid #eef2f6;
        }
        .stat-card h3 { font-size: 0.8rem; color: #7a8e9e; text-transform: uppercase; letter-spacing: 0.5px; }
        .stat-card .number { font-size: 2.5rem; font-weight: 800; margin-top: 0.3rem; }
        .stat-card .icon { float: right; font-size: 2rem; opacity: 0.2; }

        /* Sections */
        .section-card {
            background: white;
            border-radius: 24px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border: 1px solid #eef2f6;
        }
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .section-header h2 { font-size: 1.3rem; font-weight: 700; }

        .btn {
            padding: 0.5rem 1.2rem;
            border-radius: 40px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 0.85rem;
            transition: 0.2s;
        }
        .btn-primary { background: #1e2f3c; color: white; }
        .btn-primary:hover { background: #e07c4c; }
        .btn-success { background: #2e7d32; color: white; }
        .btn-success:hover { background: #1e5a22; }
        .btn-danger { background: #d9534f; color: white; }
        .btn-danger:hover { background: #b52e2a; }
        .btn-warning { background: #f0ad4e; color: white; }
        .btn-warning:hover { background: #d9922e; }
        .btn-sm { padding: 0.2rem 0.8rem; font-size: 0.75rem; }

        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 0.8rem; text-align: left; border-bottom: 1px solid #eef2f6; font-size: 0.9rem; }
        th { background: #f8fafc; font-weight: 600; color: #4a5a6e; }
        tr:hover { background: #f8fafc; }
        .status-badge {
            padding: 0.2rem 0.8rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
        }
        .status-approved { background: #dcfce7; color: #2e7d32; }
        .status-pending { background: #fef3c7; color: #b45309; }
        .status-available { background: #dcfce7; color: #2e7d32; }
        .status-sold { background: #fee2e2; color: #b91c1c; }

        .msg {
            padding: 1rem;
            border-radius: 16px;
            margin-bottom: 1.5rem;
            background: #dcfce7;
            color: #166534;
        }
        .msg.error { background: #fee2e2; color: #b91c1c; }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        .modal.active { display: flex; }
        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: 28px;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
        }
        .modal-close {
            position: absolute;
            top: 1rem;
            right: 1.5rem;
            font-size: 1.5rem;
            cursor: pointer;
            color: #7a8e9e;
        }
        .modal-content h2 { margin-bottom: 1.5rem; }
        .modal-content label { display: block; font-weight: 600; margin-top: 1rem; font-size: 0.85rem; }
        .modal-content input, .modal-content select, .modal-content textarea {
            width: 100%;
            padding: 0.7rem;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            font-family: inherit;
            margin-top: 0.3rem;
        }
        .modal-content .btn { margin-top: 1.5rem; width: 100%; padding: 0.8rem; }

        @media (max-width: 700px) {
            .container { padding: 1rem; }
            table { font-size: 0.8rem; }
            th, td { padding: 0.5rem; }
        }
    </style>
</head>
<body>

<!-- ========== HEADER ========== -->
<div class="admin-header">
    <h1><i class="fas fa-shield-alt"></i> Pastimes Admin</h1>
    <div>
        <span style="color:#a4b3c4;">👤 <?php echo htmlspecialchars($_SESSION['user']); ?></span>
        <a href="index.php"><i class="fas fa-store"></i> Store</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</div>

<!-- ========== MAIN CONTENT ========== -->
<div class="container">

    <?php if($msg): ?>
        <div class="msg">✅ <?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="icon"><i class="fas fa-users"></i></div>
            <h3>Total Users</h3>
            <div class="number"><?php echo $stats['total_users']; ?></div>
        </div>
        <div class="stat-card">
            <div class="icon"><i class="fas fa-tshirt"></i></div>
            <h3>Products</h3>
            <div class="number"><?php echo $stats['total_products']; ?></div>
        </div>
        <div class="stat-card" style="border-color: #fef3c7;">
            <div class="icon"><i class="fas fa-clock"></i></div>
            <h3>Pending Users</h3>
            <div class="number" style="color:#b45309;"><?php echo $stats['pending_users']; ?></div>
        </div>
        <div class="stat-card">
            <div class="icon"><i class="fas fa-shopping-cart"></i></div>
            <h3>Total Orders</h3>
            <div class="number"><?php echo $stats['total_orders']; ?></div>
        </div>
    </div>

    <!-- ===== PENDING USERS ===== -->
    <div class="section-card">
        <div class="section-header">
            <h2><i class="fas fa-user-clock"></i> Pending Approvals</h2>
            <span class="status-badge status-pending"><?php echo $stats['pending_users']; ?> pending</span>
        </div>
        <table>
            <tr>
                <th>Name</th>
                <th>Username</th>
                <th>Email</th>
                <th>Registered</th>
                <th>Action</th>
            </tr>
            <?php 
            $pending_query = mysqli_query($conn, "SELECT * FROM tblUser WHERE status='Pending' ORDER BY created_at DESC");
            if(mysqli_num_rows($pending_query) == 0): ?>
                <tr><td colspan="5" style="text-align:center;color:#7a8e9e;padding:2rem;">No pending users</td></tr>
            <?php else:
                while($p = mysqli_fetch_assoc($pending_query)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($p['name']); ?></td>
                        <td><?php echo htmlspecialchars($p['username']); ?></td>
                        <td><?php echo htmlspecialchars($p['email']); ?></td>
                        <td><?php echo date('d M Y', strtotime($p['created_at'] ?? 'now')); ?></td>
                        <td>
                            <a href="?action=approve&id=<?php echo $p['userID']; ?>" class="btn btn-success btn-sm"><i class="fas fa-check"></i> Approve</a>
                        </td>
                    </tr>
                <?php endwhile;
            endif; ?>
        </table>
    </div>

    <!-- ===== ALL USERS ===== -->
    <div class="section-card">
        <div class="section-header">
            <h2><i class="fas fa-users"></i> All Users</h2>
            <button class="btn btn-primary" onclick="openModal('addUserModal')"><i class="fas fa-plus"></i> Add User</button>
        </div>
        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            <?php 
            $all_users = mysqli_query($conn, "SELECT * FROM tblUser ORDER BY userID DESC");
            while($u = mysqli_fetch_assoc($all_users)): ?>
                <tr>
                    <td>#<?php echo $u['userID']; ?></td>
                    <td><?php echo htmlspecialchars($u['name']); ?></td>
                    <td><?php echo htmlspecialchars($u['username']); ?></td>
                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                    <td><?php echo htmlspecialchars($u['role'] ?? 'User'); ?></td>
                    <td>
                        <span class="status-badge <?php echo $u['status'] == 'Approved' ? 'status-approved' : 'status-pending'; ?>">
                            <?php echo $u['status']; ?>
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-primary btn-sm" onclick="editUser(<?php echo htmlspecialchars(json_encode($u)); ?>)"><i class="fas fa-edit"></i></button>
                        <a href="?action=delete_user&id=<?php echo $u['userID']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this user?')"><i class="fas fa-trash"></i></a>
                        <?php if($u['status'] == 'Pending'): ?>
                            <a href="?action=approve&id=<?php echo $u['userID']; ?>" class="btn btn-success btn-sm"><i class="fas fa-check"></i></a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <!-- ===== PRODUCTS ===== -->
    <div class="section-card">
        <div class="section-header">
            <h2><i class="fas fa-tshirt"></i> Products</h2>
            <button class="btn btn-primary" onclick="openModal('addProductModal')"><i class="fas fa-plus"></i> Add Product</button>
        </div>
        <table>
            <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Name</th>
                <th>Brand</th>
                <th>Price</th>
                <th>Qty</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            <?php 
            $all_products = mysqli_query($conn, "SELECT c.*, cat.categoryName FROM tblClothes c LEFT JOIN tblCategories cat ON c.categoryID = cat.categoryID ORDER BY c.itemID DESC");
            while($p = mysqli_fetch_assoc($all_products)): 
                $has_image = $p['image'] && file_exists("images/".$p['image']);
            ?>
                <tr>
                    <td>#<?php echo $p['itemID']; ?></td>
                    <td>
                        <?php if($has_image): ?>
                            <img src="images/<?php echo htmlspecialchars($p['image']); ?>" style="width:40px;height:40px;object-fit:cover;border-radius:8px;">
                        <?php else: ?>
                            <i class="fas fa-tshirt" style="font-size:24px;opacity:0.3;"></i>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($p['name']); ?></td>
                    <td><?php echo htmlspecialchars($p['brand'] ?? '-'); ?></td>
                    <td>R <?php echo number_format($p['price'], 2); ?></td>
                    <td><?php echo $p['quantity'] ?? 0; ?></td>
                    <td>
                        <span class="status-badge <?php echo ($p['status'] ?? 'Available') == 'Available' ? 'status-approved' : 'status-sold'; ?>">
                            <?php echo $p['status'] ?? 'Available'; ?>
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-primary btn-sm" onclick="editProduct(<?php echo htmlspecialchars(json_encode($p)); ?>)"><i class="fas fa-edit"></i></button>
                        <a href="?action=delete_product&id=<?php echo $p['itemID']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this product?')"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>

</div>

<!-- ===== MODALS ===== -->

<!-- Add User Modal -->
<div class="modal" id="addUserModal">
    <div class="modal-content">
        <span class="modal-close" onclick="closeModal('addUserModal')">&times;</span>
        <h2><i class="fas fa-user-plus"></i> Add User</h2>
        <form method="POST" action="">
            <input type="hidden" name="action" value="add_user">
            <label>Full Name *</label>
            <input type="text" name="name" required>
            <label>Email *</label>
            <input type="email" name="email" required>
            <label>Username *</label>
            <input type="text" name="username" required>
            <label>Password *</label>
            <input type="password" name="password" required minlength="6">
            <label>Role</label>
            <select name="role">
                <option value="User">User</option>
                <option value="Admin">Admin</option>
            </select>
            <label>Status</label>
            <select name="status">
                <option value="Pending">Pending</option>
                <option value="Approved">Approved</option>
            </select>
            <label>Phone</label>
            <input type="text" name="phone">
            <label>Address</label>
            <textarea name="address"></textarea>
            <button type="submit" class="btn btn-primary">Add User</button>
        </form>
    </div>
</div>

<!-- Add Product Modal -->
<div class="modal" id="addProductModal">
    <div class="modal-content">
        <span class="modal-close" onclick="closeModal('addProductModal')">&times;</span>
        <h2><i class="fas fa-plus-circle"></i> Add Product</h2>
        <form method="POST" action="" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add_clothing">
            <label>Product Name *</label>
            <input type="text" name="name" required>
            <label>Description</label>
            <textarea name="description"></textarea>
            <label>Brand</label>
            <input type="text" name="brand">
            <label>Price (R) *</label>
            <input type="number" name="price" step="0.01" required>
            <label>Original Price (R) - for discounts</label>
            <input type="number" name="original_price" step="0.01">
            <label>Quantity *</label>
            <input type="number" name="quantity" min="0" required>
            <label>Category</label>
            <select name="categoryID">
                <?php 
                $cats = mysqli_query($conn, "SELECT * FROM tblCategories");
                while($cat = mysqli_fetch_assoc($cats)): ?>
                    <option value="<?php echo $cat['categoryID']; ?>"><?php echo htmlspecialchars($cat['categoryName']); ?></option>
                <?php endwhile; ?>
            </select>
            <label>Condition</label>
            <select name="condition">
                <option value="New">New with tags</option>
                <option value="Excellent">Excellent</option>
                <option value="Good">Good</option>
                <option value="Fair">Fair</option>
            </select>
            <label>Size</label>
            <select name="size">
                <option value="XS">XS</option>
                <option value="S">S</option>
                <option value="M">M</option>
                <option value="L">L</option>
                <option value="XL">XL</option>
                <option value="XXL">XXL</option>
            </select>
            <label>Status</label>
            <select name="status">
                <option value="Available">Available</option>
                <option value="Sold">Sold</option>
            </select>
            <label>Product Image</label>
            <input type="file" name="image" accept="image/*">
            <button type="submit" class="btn btn-primary">Add Product</button>
        </form>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal" id="editUserModal">
    <div class="modal-content">
        <span class="modal-close" onclick="closeModal('editUserModal')">&times;</span>
        <h2><i class="fas fa-user-edit"></i> Edit User</h2>
        <form method="POST" action="">
            <input type="hidden" name="action" value="update_user">
            <input type="hidden" name="userID" id="edit_user_id">
            <label>Full Name *</label>
            <input type="text" name="name" id="edit_user_name" required>
            <label>Email *</label>
            <input type="email" name="email" id="edit_user_email" required>
            <label>Username *</label>
            <input type="text" name="username" id="edit_user_username" required>
            <label>Role</label>
            <select name="role" id="edit_user_role">
                <option value="User">User</option>
                <option value="Admin">Admin</option>
            </select>
            <label>Status</label>
            <select name="status" id="edit_user_status">
                <option value="Pending">Pending</option>
                <option value="Approved">Approved</option>
            </select>
            <label>Phone</label>
            <input type="text" name="phone" id="edit_user_phone">
            <label>Address</label>
            <textarea name="address" id="edit_user_address"></textarea>
            <button type="submit" class="btn btn-primary">Update User</button>
        </form>
    </div>
</div>

<!-- Edit Product Modal -->
<div class="modal" id="editProductModal">
    <div class="modal-content">
        <span class="modal-close" onclick="closeModal('editProductModal')">&times;</span>
        <h2><i class="fas fa-edit"></i> Edit Product</h2>
        <form method="POST" action="" enctype="multipart/form-data">
            <input type="hidden" name="action" value="update_clothing">
            <input type="hidden" name="itemID" id="edit_product_id">
            <label>Product Name *</label>
            <input type="text" name="name" id="edit_product_name" required>
            <label>Description</label>
            <textarea name="description" id="edit_product_desc"></textarea>
            <label>Brand</label>
            <input type="text" name="brand" id="edit_product_brand">
            <label>Price (R) *</label>
            <input type="number" name="price" id="edit_product_price" step="0.01" required>
            <label>Original Price (R)</label>
            <input type="number" name="original_price" id="edit_product_original" step="0.01">
            <label>Quantity *</label>
            <input type="number" name="quantity" id="edit_product_qty" min="0" required>
            <label>Category</label>
            <select name="categoryID" id="edit_product_category">
                <?php 
                $cats = mysqli_query($conn, "SELECT * FROM tblCategories");
                while($cat = mysqli_fetch_assoc($cats)): ?>
                    <option value="<?php echo $cat['categoryID']; ?>"><?php echo htmlspecialchars($cat['categoryName']); ?></option>
                <?php endwhile; ?>
            </select>
            <label>Condition</label>
            <select name="condition" id="edit_product_condition">
                <option value="New">New with tags</option>
                <option value="Excellent">Excellent</option>
                <option value="Good">Good</option>
                <option value="Fair">Fair</option>
            </select>
            <label>Size</label>
            <select name="size" id="edit_product_size">
                <option value="XS">XS</option>
                <option value="S">S</option>
                <option value="M">M</option>
                <option value="L">L</option>
                <option value="XL">XL</option>
                <option value="XXL">XXL</option>
            </select>
            <label>Status</label>
            <select name="status" id="edit_product_status">
                <option value="Available">Available</option>
                <option value="Sold">Sold</option>
            </select>
            <label>Change Image (optional)</label>
            <input type="file" name="image" accept="image/*">
            <button type="submit" class="btn btn-primary">Update Product</button>
        </form>
    </div>
</div>

<script>
// Modal functions
function openModal(id) {
    document.getElementById(id).classList.add('active');
}
function closeModal(id) {
    document.getElementById(id).classList.remove('active');
}
// Close modal on click outside
document.querySelectorAll('.modal').forEach(m => {
    m.addEventListener('click', function(e) {
        if(e.target === this) this.classList.remove('active');
    });
});

// Edit user
function editUser(user) {
    document.getElementById('edit_user_id').value = user.userID;
    document.getElementById('edit_user_name').value = user.name;
    document.getElementById('edit_user_email').value = user.email;
    document.getElementById('edit_user_username').value = user.username;
    document.getElementById('edit_user_role').value = user.role || 'User';
    document.getElementById('edit_user_status').value = user.status;
    document.getElementById('edit_user_phone').value = user.phone || '';
    document.getElementById('edit_user_address').value = user.address || '';
    openModal('editUserModal');
}

// Edit product
function editProduct(product) {
    document.getElementById('edit_product_id').value = product.itemID;
    document.getElementById('edit_product_name').value = product.name;
    document.getElementById('edit_product_desc').value = product.description || '';
    document.getElementById('edit_product_brand').value = product.brand || '';
    document.getElementById('edit_product_price').value = product.price;
    document.getElementById('edit_product_original').value = product.original_price || product.price;
    document.getElementById('edit_product_qty').value = product.quantity || 0;
    document.getElementById('edit_product_category').value = product.categoryID || '';
    document.getElementById('edit_product_condition').value = product.condition || 'Good';
    document.getElementById('edit_product_size').value = product.size || 'M';
    document.getElementById('edit_product_status').value = product.status || 'Available';
    openModal('editProductModal');
}
</script>

</body>
</html>