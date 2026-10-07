<?php 
if(session_status() === PHP_SESSION_NONE) { session_start(); }
include_once __DIR__.'/db.php';

// ป้องกันคนที่ไม่ใช่แอดมิน
if(!is_admin()){ header('HTTP/1.1 403 Forbidden'); echo 'ต้องเป็นแอดมินเท่านั้น'; exit; }

$msg = '';
$error = '';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    
    // ================= 1. ระบบลบสินค้า (Delete) =================
    if(($_POST['action'] ?? '') === 'delete' && !empty($_POST['id'])){
        $delId = intval($_POST['id']);
        
        // get image path to unlink
        $stmt = $conn->prepare('SELECT image FROM products WHERE id=? LIMIT 1');
        $stmt->bind_param('i',$delId); $stmt->execute(); $res = $stmt->get_result();
        if($row = $res->fetch_assoc()){
            $img = $row['image'];
            if($img){
                // convert web path to file path and unlink if exists
                $rel = preg_replace('#^/shoestore#','',$img);
                $fp = __DIR__ . $rel;
                if(file_exists($fp)) @unlink($fp);
            }
        }
        $d = $conn->prepare('DELETE FROM products WHERE id=?');
        $d->bind_param('i',$delId);
        
        if($d->execute()) { 
            $_SESSION['msg'] = 'ลบสินค้าสำเร็จ'; 
        } else { 
            $_SESSION['error'] = 'ไม่สามารถลบสินค้า: '.$conn->error; 
        }
        header('Location: admin.php'); 
        exit;
    }

    // ================= 2. ระบบเพิ่มสินค้า (Add) =================
    if(($_POST['action'] ?? '') === 'add') {
        $name = trim($_POST['name'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $category = trim($_POST['category'] ?? 'Sneakers'); // รับค่าหมวดหมู่
        $is_popular = isset($_POST['is_popular']) ? 1 : 0;  // ตรวจสอบว่าติ๊กสินค้ายอดฮิตไหม
        $imgPath = null;

        // Handle image upload
        if(!empty($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK){
            $uploaddir = __DIR__ . '/uploads';
            if(!is_dir($uploaddir)) mkdir($uploaddir, 0755, true);
            $f = $_FILES['image'];
            $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','gif', 'webp'];
            if(in_array($ext, $allowed)){
                $filename = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                $dest = $uploaddir . '/' . $filename;
                if(move_uploaded_file($f['tmp_name'], $dest)){
                    // store web-accessible path
                    $imgPath = '/shoestore/uploads/' . $filename;
                } else {
                    $error = 'ไม่สามารถอัปโหลดรูปได้';
                }
            } else {
                $error = 'ชนิดไฟล์ไม่รองรับ (jpg, png, gif, webp)';
            }
        }

        if(!$name || $price <= 0){
            $error = $error ?: 'โปรดกรอกชื่อและราคาที่ถูกต้อง';
        } else {
            // เพิ่ม category และ is_popular ลงในฐานข้อมูล
            $stmt = $conn->prepare('INSERT INTO products (name, description, price, image, category, is_popular) VALUES (?,?,?,?,?,?)');
            $stmt->bind_param('ssdssi', $name, $desc, $price, $imgPath, $category, $is_popular);
            if($stmt->execute()){ 
                $msg = 'เพิ่มสินค้าเรียบร้อย'; 
            } else { 
                $error = 'Error: '.$conn->error; 
            }
        }
    }
}

// รับข้อความแจ้งเตือนจากการลบ (เพื่อป้องกันการ Refresh หน้าแล้วถามซ้ำ)
if(isset($_SESSION['msg'])) { $msg = $_SESSION['msg']; unset($_SESSION['msg']); }
if(isset($_SESSION['error'])) { $error = $_SESSION['error']; unset($_SESSION['error']); }

include_once __DIR__.'/header.php';
?>

<style>
    /* ดีไซน์การ์ดฟอร์มเพิ่มสินค้า */
    .admin-card {
        background: var(--card, #fff);
        padding: 30px;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.04);
        margin-bottom: 40px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    @media (max-width: 768px) {
        .form-grid { grid-template-columns: 1fr; }
    }

    .form-group { margin-bottom: 15px; }
    .form-group label {
        display: block;
        font-weight: bold;
        color: var(--dark);
        margin-bottom: 8px;
    }
    .form-control {
        width: 100%;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-family: inherit;
        outline: none;
        transition: border-color 0.2s;
    }
    .form-control:focus { border-color: var(--primary); }
    
    /* กล่องอัปโหลดไฟล์ */
    .file-upload-box {
        border: 2px dashed #ddd;
        padding: 15px;
        border-radius: 8px;
        background: #fcfcfc;
    }

    /* Checkbox สินค้ายอดฮิต */
    .checkbox-container {
        display: flex;
        align-items: center;
        gap: 10px;
        background: #fffdf5;
        border: 1px solid #ffeaa7;
        padding: 12px;
        border-radius: 8px;
        cursor: pointer;
    }
    .checkbox-container input { width: 18px; height: 18px; cursor: pointer; }

    .btn-submit {
        background: #27ae60;
        color: white;
        border: none;
        padding: 12px 25px;
        font-size: 16px;
        font-weight: bold;
        border-radius: 8px;
        cursor: pointer;
        width: 100%;
        transition: transform 0.2s;
    }
    .btn-submit:hover { transform: translateY(-2px); background: #219653; }

    /* แจ้งเตือน */
    .alert { padding: 15px 20px; border-radius: 12px; margin-bottom: 20px; font-weight: bold; }
    .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

    /* ป้ายสินค้า */
    .badge-category {
        position: absolute;
        top: 10px;
        left: 10px;
        background: var(--dark);
        color: white;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: bold;
    }
    .badge-hot {
        position: absolute;
        top: 10px;
        right: 10px;
        background: #e74c3c;
        color: white;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: bold;
        box-shadow: 0 2px 5px rgba(231,76,60,0.3);
    }
</style>

<main style="max-width: 1200px; margin: 0 auto; padding: 20px;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="margin: 0; color: var(--dark); font-size: 28px;">📦 จัดการคลังสินค้า (แอดมิน)</h2>
        <a href="admin_dashboard.php" style="color: var(--muted); text-decoration: none; font-weight: bold;">← กลับหน้าแดชบอร์ด</a>
    </div>

    <?php if(!empty($msg)) echo '<div class="alert alert-success">✅ '.htmlspecialchars($msg).'</div>'; ?>
    <?php if(!empty($error)) echo '<div class="alert alert-error">❌ '.htmlspecialchars($error).'</div>'; ?>

    <div class="admin-card">
        <h3 style="margin-top: 0; border-bottom: 2px solid #eee; padding-bottom: 15px; margin-bottom: 20px;">➕ เพิ่มสินค้าใหม่</h3>
        
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add">
            
            <div class="form-grid">
                <div>
                    <div class="form-group">
                        <label>ชื่อสินค้า <span style="color:red;">*</span></label>
                        <input type="text" name="name" class="form-control" required placeholder="เช่น Nike Air Max 97">
                    </div>
                    <div class="form-group">
                        <label>ราคา (บาท) <span style="color:red;">*</span></label>
                        <input type="number" name="price" class="form-control" step="0.01" required placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label>หมวดหมู่สินค้า</label>
                        <select name="category" class="form-control">
                            <option value="Sneakers">👟 รองเท้าสตรีทแวร์ (Sneakers)</option>
                            <option value="Running">🏃‍♂️ รองเท้าวิ่ง (Running)</option>
                            <option value="Sandals">🩴 รองเท้าแตะ (Sandals)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <div class="form-group">
                        <label>รูปภาพสินค้า</label>
                        <div class="file-upload-box">
                            <input type="file" name="image" accept="image/*" style="width: 100%;">
                            <small style="color: var(--muted); display: block; margin-top: 5px;">* รองรับ: jpg, png, gif, webp</small>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>คำอธิบายสินค้า</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="รายละเอียดของสินค้ารุ่นนี้..."></textarea>
                    </div>
                </div>
            </div>
            
            <div style="margin-top: 25px; text-align: right;">
                <button type="submit" class="btn-submit">💾 บันทึกสินค้าใหม่</button>
            </div>
        </form>
    </div>

    <h3 style="margin-top: 0; margin-bottom: 20px; color: var(--dark);">📋 รายการสินค้าในระบบ</h3>
    
    <?php
    // ดึง category และ is_popular มาด้วยเพื่อแสดงป้าย
    $r = $conn->query('SELECT id, name, price, image, category, is_popular FROM products ORDER BY created_at DESC');
    
    echo '<div class="grid">';

    while($p = $r->fetch_assoc()){
        // ถ้าระบุว่าฮิต ให้กล่องเป็นสีครีมๆ แบบหน้าแรก
        $card_style = $p['is_popular'] ? 'border: 1px solid #ffeaa7; background: #fffdf5;' : '';
        
        echo '<div class="product" style="position: relative; ' . $card_style . '">';
        
        // ป้าย Category
        $cat = $p['category'] ?? 'Sneakers';
        echo '<div class="badge-category">' . htmlspecialchars($cat) . '</div>';
        
        // ป้าย Hot
        if($p['is_popular']){
            echo '<div class="badge-hot">HOT 🔥</div>';
        }
        
        // รูปภาพ
        $imgSrc = !empty($p['image']) ? htmlspecialchars($p['image']) : 'placeholder.png';
        echo '<img src="'.$imgSrc.'" alt="'.htmlspecialchars($p['name']).'">';
        
        // ชื่อและราคา
        echo '<h3>'.htmlspecialchars($p['name']).'</h3>';
        echo '<p class="price" style="'.($p['is_popular'] ? 'color:#d35400;' : '').'">'.number_format($p['price'], 2).' ฿</p>';
        
        // กลุ่มปุ่ม แก้ไข / ลบ
        echo '<div style="display:flex; gap:8px; margin-top:15px;">';
        
        // ปุ่มแก้ไข (ไปหน้า edit_product.php)
        echo '<a href="edit_product.php?id='.intval($p['id']).'" style="flex:1; background:#f39c12; color:#fff; text-decoration:none; padding:10px; border-radius:8px; text-align:center; font-size:14px; font-weight:bold;">✏️ แก้ไข</a>';
        
        // ปุ่มลบ
        echo '<form method="post" onsubmit="return confirm(\'ยืนยันการลบสินค้านี้ใช่หรือไม่? (การกระทำนี้ไม่สามารถกู้คืนได้)\')" style="flex:1; margin:0;">';
        echo '<input type="hidden" name="action" value="delete">';
        echo '<input type="hidden" name="id" value="'.intval($p['id']).'">';
        echo '<button style="width:100%; background:#e74c3c; color:#fff; padding:10px; border-radius:8px; border:none; cursor:pointer; font-size:14px; font-weight:bold;">🗑️ ลบ</button>';
        echo '</form>';
        
        echo '</div>'; // ปิด div จัดกลุ่มปุ่ม
        echo '</div>'; // ปิด div .product
    }

    echo '</div>';
    ?>
</main>

<?php include_once __DIR__.'/footer.php'; ?>