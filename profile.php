<?php
if(session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__.'/db.php';

// เช็คว่าล็อกอินหรือยัง ถ้ายังให้เด้งไปหน้า login
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user']['id'];
$is_admin_user = is_admin(); // 🌟 เช็คว่าเป็นแอดมินหรือไม่
$msg = '';
$error = '';

// เช็คว่าตอนนี้กดอยู่หน้าแท็บไหน (ค่าเริ่มต้นคือแท็บ profile)
$active_tab = $_GET['tab'] ?? 'profile';

// ป้องกันแอดมินแอบเข้าแท็บที่อยู่จัดส่งผ่าน URL
if ($is_admin_user && $active_tab === 'address') {
    $active_tab = 'profile'; 
}

// ================= 1. จัดการการบันทึกข้อมูล (POST) =================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $active_tab = $_POST['current_tab'] ?? 'profile';

    // 📌 กรณีแก้ไขข้อมูลส่วนตัว (ชื่อ, เบอร์โทร)
    if ($action === 'update_profile') {
        $full_name = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        $update_stmt = $conn->prepare("UPDATE users SET full_name = ?, phone = ? WHERE id = ?");
        $update_stmt->bind_param("ssi", $full_name, $phone, $user_id);
        
        if ($update_stmt->execute()) {
            $msg = "บันทึกข้อมูลส่วนตัวเรียบร้อยแล้ว!";
        } else {
            $error = "เกิดข้อผิดพลาดในการบันทึกข้อมูล";
        }
    }

    // 📌 กรณีแก้ไขที่อยู่จัดส่ง (อนุญาตเฉพาะลูกค้า)
    if ($action === 'update_address' && !$is_admin_user) {
        $address = trim($_POST['address'] ?? '');

        $update_stmt = $conn->prepare("UPDATE users SET address = ? WHERE id = ?");
        $update_stmt->bind_param("si", $address, $user_id);
        
        if ($update_stmt->execute()) {
            $msg = "อัปเดตที่อยู่จัดส่งสำเร็จ!";
        } else {
            $error = "เกิดข้อผิดพลาดในการบันทึกที่อยู่";
        }
    }

    // 📌 กรณีเปลี่ยนรหัสผ่าน
    if ($action === 'update_password') {
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (!empty($new_password)) {
            if ($new_password === $confirm_password) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $update_stmt->bind_param("si", $hashed_password, $user_id);
                
                if ($update_stmt->execute()) {
                    $msg = "เปลี่ยนรหัสผ่านใหม่สำเร็จแล้ว!";
                } else {
                    $error = "เกิดข้อผิดพลาดในการเปลี่ยนรหัสผ่าน";
                }
            } else {
                $error = "รหัสผ่านใหม่ไม่ตรงกัน กรุณาลองอีกครั้ง";
            }
        } else {
            $error = "กรุณากรอกรหัสผ่านใหม่ที่ต้องการเปลี่ยน";
        }
    }
}

// ================= 2. ดึงข้อมูลล่าสุดมาแสดง =================
$stmt = $conn->prepare("SELECT username, email, full_name, phone, address FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

include_once __DIR__.'/header.php';
?>

<style>
    .account-layout { display: flex; max-width: 1100px; margin: 40px auto; gap: 30px; padding: 0 20px; align-items: flex-start; }
    .sidebar-menu { width: 250px; background: transparent; flex-shrink: 0; }
    .user-brief { display: flex; align-items: center; gap: 15px; padding-bottom: 20px; border-bottom: 1px solid #ddd; margin-bottom: 20px; }
    .user-avatar-small { width: 50px; height: 50px; background: #eef2ff; color: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: bold; border: 2px solid var(--primary); }
    .user-name { font-weight: bold; color: var(--dark); font-size: 16px; }
    .user-edit-text { color: var(--muted); font-size: 13px; }
    .menu-list { list-style: none; padding: 0; margin: 0; }
    .menu-item { margin-bottom: 5px; }
    .menu-link { display: flex; align-items: center; gap: 10px; padding: 12px 15px; color: #555; text-decoration: none; border-radius: 8px; font-weight: 500; transition: all 0.2s; }
    .menu-link:hover { background: #f4f6f8; color: var(--primary); }
    .menu-link.active { background: #eef2ff; color: var(--primary); font-weight: bold; }
    .content-card { flex: 1; background: var(--card, #fff); padding: 40px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.04); min-width: 0; }
    .content-header { margin-bottom: 30px; border-bottom: 2px solid #f0f0f0; padding-bottom: 15px; }
    .content-title { margin: 0; font-size: 22px; color: var(--dark); }
    .content-subtitle { margin: 5px 0 0 0; color: var(--muted); font-size: 14px; }
    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; font-weight: 600; color: #444; margin-bottom: 8px; font-size: 14px; }
    .form-control { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 15px; font-family: inherit; outline: none; transition: border-color 0.2s; }
    .form-control:focus { border-color: var(--primary); }
    .form-control[readonly] { background: #f9f9f9; color: #888; cursor: not-allowed; }
    .btn-save { background: var(--primary); color: white; border: none; padding: 12px 30px; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: pointer; transition: transform 0.2s; display: inline-block; }
    .btn-save:hover { transform: translateY(-2px); opacity: 0.9; }
    .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: bold; text-align: center; }
    .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    @media (max-width: 768px) { .account-layout { flex-direction: column; } .sidebar-menu { width: 100%; border-bottom: 1px solid #ddd; padding-bottom: 20px; } .content-card { padding: 25px 20px; } }
</style>

<main>
    <div class="account-layout">
        
        <aside class="sidebar-menu">
            <div class="user-brief">
                <div class="user-avatar-small">
                    <?php echo $is_admin_user ? '🛡️' : '👤'; ?>
                </div>
                <div>
                    <div class="user-name"><?php echo htmlspecialchars($user['username']); ?> <?php echo $is_admin_user ? '<span style="color:#e74c3c; font-size:12px;">(Admin)</span>' : ''; ?></div>
                    <div class="user-edit-text">✏️ จัดการบัญชีของคุณ</div>
                </div>
            </div>

            <ul class="menu-list">
                <li class="menu-item">
                    <a href="?tab=profile" class="menu-link <?php echo $active_tab === 'profile' ? 'active' : ''; ?>">
                        📋 ข้อมูลส่วนตัว
                    </a>
                </li>
                
                <?php if(!$is_admin_user): ?>
                    <li class="menu-item">
                        <a href="?tab=address" class="menu-link <?php echo $active_tab === 'address' ? 'active' : ''; ?>">
                            📍 ที่อยู่สำหรับจัดส่ง
                        </a>
                    </li>
                <?php endif; ?>

                <li class="menu-item">
                    <a href="?tab=password" class="menu-link <?php echo $active_tab === 'password' ? 'active' : ''; ?>">
                        🔐 เปลี่ยนรหัสผ่าน
                    </a>
                </li>
                
                <?php if(!$is_admin_user): ?>
                    <li class="menu-item" style="margin-top: 20px;">
                        <a href="my_orders.php" class="menu-link" style="color: #e67e22;">
                            📦 ประวัติการสั่งซื้อของฉัน
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </aside>

        <div class="content-card">
            
            <?php if(!empty($msg)) echo '<div class="alert alert-success">✅ '.htmlspecialchars($msg).'</div>'; ?>
            <?php if(!empty($error)) echo '<div class="alert alert-error">❌ '.htmlspecialchars($error).'</div>'; ?>

            <?php if($active_tab === 'profile'): ?>
                <div class="content-header">
                    <h2 class="content-title">ข้อมูลส่วนตัว</h2>
                    <p class="content-subtitle">จัดการข้อมูลส่วนตัวของคุณเพื่อความสะดวกในการติดต่อ</p>
                </div>
                <form method="post">
                    <input type="hidden" name="action" value="update_profile">
                    <input type="hidden" name="current_tab" value="profile">

                    <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                        <div class="form-group" style="flex: 1; min-width: 200px;">
                            <label>ชื่อผู้ใช้ (Username)</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                        </div>
                        <div class="form-group" style="flex: 1; min-width: 200px;">
                            <label>อีเมล (Email)</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>ชื่อ-นามสกุลจริง</label>
                        <input type="text" name="full_name" class="form-control" placeholder="ชื่อ นามสกุล" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label>เบอร์โทรศัพท์</label>
                        <input type="text" name="phone" class="form-control" placeholder="เบอร์โทรศัพท์ที่ติดต่อได้" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                    </div>

                    <button type="submit" class="btn-save">บันทึกข้อมูลส่วนตัว</button>
                </form>
            <?php endif; ?>

            <?php if($active_tab === 'address' && !$is_admin_user): ?>
                <div class="content-header">
                    <h2 class="content-title">ที่อยู่สำหรับจัดส่ง</h2>
                    <p class="content-subtitle">ตั้งค่าที่อยู่หลักสำหรับการรับสินค้าของคุณ</p>
                </div>
                <form method="post">
                    <input type="hidden" name="action" value="update_address">
                    <input type="hidden" name="current_tab" value="address">

                    <div class="form-group">
                        <label>ที่อยู่จัดส่งแบบเต็ม (บ้านเลขที่, ถนน, ซอย, ตำบล, อำเภอ, จังหวัด, รหัสไปรษณีย์)</label>
                        <textarea name="address" class="form-control" rows="5" placeholder="กรอกที่อยู่สำหรับจัดส่งพัสดุ..."><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                    </div>

                    <button type="submit" class="btn-save">บันทึกที่อยู่</button>
                </form>
            <?php endif; ?>

            <?php if($active_tab === 'password'): ?>
                <div class="content-header">
                    <h2 class="content-title">เปลี่ยนรหัสผ่าน</h2>
                    <p class="content-subtitle">ตั้งรหัสผ่านใหม่เพื่อความปลอดภัยของบัญชีคุณ</p>
                </div>
                <form method="post">
                    <input type="hidden" name="action" value="update_password">
                    <input type="hidden" name="current_tab" value="password">

                    <div class="form-group">
                        <label>รหัสผ่านใหม่</label>
                        <input type="password" name="new_password" class="form-control" placeholder="อย่างน้อย 6 ตัวอักษร" minlength="6" required>
                    </div>
                    
                    <div class="form-group">
                        <label>ยืนยันรหัสผ่านใหม่</label>
                        <input type="password" name="confirm_password" class="form-control" placeholder="กรอกรหัสผ่านใหม่อีกครั้ง" minlength="6" required>
                    </div>
                    
                    <button type="submit" class="btn-save" style="background: #e74c3c;">ยืนยันการเปลี่ยนรหัสผ่าน</button>
                </form>
            <?php endif; ?>

        </div>
    </div>
</main>

<?php include_once __DIR__.'/footer.php'; ?>