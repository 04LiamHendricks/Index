<?php
// ============================================
// File: seller_request.php - Seller Request
// ============================================
session_start();
include("includes/DBConn.php");

if(!isset($_SESSION['user'])){
    header("Location: login.php");
    exit();
}

$username = $_SESSION['user'];
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM tblUser WHERE username='$username'"));

$success = '';
$error = '';

if(isset($_POST['submit'])){
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    $brand = sanitize($_POST['brand'] ?? '');
    $price = (float)$_POST['price'];
    $category = sanitize($_POST['category']);
    $condition = sanitize($_POST['condition']);
    $size = sanitize($_POST['size']);
    $contact = sanitize($_POST['contact']);
    
    // Handle image
    $image = '';
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "images/";
        if(!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $image = time() . '_' . basename($_FILES['image']['name']);
        $target_file = $target_dir . $image;
        move_uploaded_file($_FILES['image']['tmp_name'], $target_file);
    }
    
    $query = "INSERT INTO tblSellerRequests (userID, name, description, brand, price, category, condition, size, contact, image, status, created_at) 
              VALUES ({$user['userID']}, '$name', '$description', '$brand', $price, '$category', '$condition', '$size', '$contact', '$image', 'Pending', NOW())";
    
    if(mysqli_query($conn, $query)){
        $success = "Your request has been submitted! An admin will review it shortly.";
    } else {
        $error = "Error: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Sell with Pastimes</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #f5f7fb;
            padding: 2rem;
        }
        .container { max-width: 700px; margin: 0 auto; }
        .card {
            background: white;
            border-radius: 28px;
            padding: 2.5rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            border: 1px solid #eef2f6;
        }
        .card h1 { font-weight: 800; margin-bottom: 0.5rem; }
        .card p { color: #7a8e9e; margin-bottom: 1.5rem; }
        label { display: block; font-weight: 600; margin-top: 1rem; font-size: 0.85rem; }
        input, select, textarea {
            width: 100%;
            padding: 0.8rem;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            font-family: inherit;
            margin-top: 0.3rem;
        }
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #e07c4c;
        }
        .btn {
            background: #1e2f3c;
            color: white;
            border: none;
            padding: 1rem;
            width: 100%;
            border-radius: 40px;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            margin-top: 1.5rem;
            transition: 0.2s;
        }
        .btn:hover { background: #e07c4c; }
        .success { background: #dcfce7; color: #166534; padding: 1rem; border-radius: 16px; margin-bottom: 1rem; }
        .error { background: #fee2e2; color: #b91c1c; padding: 1rem; border-radius: 16px; margin-bottom: 1rem; }
        .back { color: #e07c4c; text-decoration: none; display: inline-block; margin-top: 1rem; }
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <h1><i class="fas fa-store-alt"></i> Sell Your Clothes</h1>
        <p>Submit a request to sell your pre-loved clothing. Admin will review and approve.</p>
        
        <?php if($success): ?>
            <div class="success">✅ <?php echo $success; ?></div>
        <?php endif; ?>
        <?php if($error): ?>
            <div class="error">❌ <?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="" enctype="multipart/form-data">
            <label>Item Name *</label>
            <input type="text" name="name" required>
            
            <label>Description *</label>
            <textarea name="description" rows="3" required></textarea>
            
            <label>Brand</label>
            <input type="text" name="brand">
            
            <label>Price (R) *</label>
            <input type="number" name="price" step="0.01" required>
            
            <label>Category</label>
            <select name="category">
                <option value="T-Shirts">T-Shirts</option>
                <option value="Hoodies">Hoodies</option>
                <option value="Jackets">Jackets</option>
                <option value="Pants">Pants</option>
                <option value="Shorts">Shorts</option>
                <option value="Accessories">Accessories</option>
                <option value="Other">Other</option>
            </select>
            
            <label>Condition *</label>
            <select name="condition" required>
                <option value="New with tags">New with tags</option>
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
            
            <label>Contact Number *</label>
            <input type="text" name="contact" required placeholder="Your phone number">
            
            <label>Product Image</label>
            <input type="file" name="image" accept="image/*">
            
            <button type="submit" name="submit" class="btn"><i class="fas fa-paper-plane"></i> Submit Request</button>
        </form>
        
        <a href="index.php" class="back">← Back to Store</a>
    </div>
</div>
</body>
</html>