<?php include_once __DIR__.'/db.php'; ?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ShoeStore</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
  <div class="nav" style="display: flex; align-items: center; justify-content: space-between; max-width: 1400px; margin: 0 auto; padding: 14px 20px; gap: 20px;">
    
    <a class="brand" href="index.php" style="white-space: nowrap; font-size: 20px; font-weight: bold; color: #fff; text-decoration: none; margin-right: auto;">ShoeStore</a>
    
    <nav style="display: flex; align-items: center; gap: 15px; flex-wrap: nowrap;">
      <a href="products.php" style="white-space: nowrap; color: #fff; text-decoration: none; font-weight: 500;">สินค้า</a>
      
      <?php if(!is_admin()): ?>
          <a href="cart.php" style="white-space: nowrap; color: #fff; text-decoration: none; font-weight: 500;">ตะกร้า</a>
      <?php endif; ?>
      
      <?php if(is_logged_in()): ?>
        
        <a href="profile.php" class="user-badge" style="display: inline-flex; align-items: center; gap: 6px; text-decoration: none; color: #fff; background: rgba(0,0,0,0.2); padding: 6px 14px; border-radius: 20px; font-weight: bold; transition: background 0.2s;" onmouseover="this.style.background='rgba(0,0,0,0.3)'" onmouseout="this.style.background='rgba(0,0,0,0.2)'">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
            <circle cx="12" cy="7" r="4"></circle>
          </svg>
          <?php echo htmlspecialchars($_SESSION['user']['username']); ?>
        </a>
        
        <?php if(is_admin()): ?>
            <a href="admin_dashboard.php" style="white-space: nowrap; text-decoration: none; padding: 6px 12px; border-radius: 6px; background: #e67e22; color: white; font-weight: bold;">แดชบอร์ด</a>
            <a href="admin.php" style="white-space: nowrap; color: #fff; text-decoration: none; font-weight: 500;">จัดการสินค้า</a>
            <a href="admin_orders.php" style="white-space: nowrap; color: #fff; text-decoration: none; font-weight: 500;">จัดการออเดอร์</a>
        <?php else: ?>
            <a href="my_orders.php" style="white-space: nowrap; color: #fff; text-decoration: none; font-weight: 500;">ออเดอร์ของฉัน</a>
        <?php endif; ?>
        
        <a href="logout.php" style="white-space: nowrap; text-decoration: none; padding: 6px 14px; border-radius: 6px; background: #e74c3c; color: #fff; font-weight: bold;">ออกจากระบบ</a>
      
      <?php else: ?>
        <a href="login.php" style="white-space: nowrap; color: #fff; text-decoration: none; font-weight: 500;">ล็อกอิน</a>
        
      <?php endif; ?>
    </nav>
  </div>
</header>
<main>