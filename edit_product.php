<?php 
if(session_status() === PHP_SESSION_NONE) { session_start(); }
include_once __DIR__.'/db.php';

// ป้องกันคนที่ไม่ใช่แอดมิน
if(!is_admin()){ header('HTTP/1.1 403 Forbidden'); echo 'ต้องเป็นแอดมินเท่านั้น'; exit; }

$msg = '';
$error = '';

// ตรวจสอบว่ามีการส่ง ID สินค้ามาหรือไม่
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: admin.php');
    exit;
}

$edit_id = intval($_GET['id']);

// ================= 1. ดึงข้อมูลสินค้าเดิมมาแสดง =================
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $edit_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    echo "<script>alert('ไม่พบสินค้านี้ในระบบ'); window.location='admin.php';</script>";
    exit;
}

// ================= 2. จัดการเมื่อแอดมินกดบันทึกการแก้ไข (POST) =================
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit'){
    $name = trim($_POST['name'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $category = trim($_POST['category'] ?? 'Sneakers');
    $is_popular = isset($_POST['is_popular']) ? 1 : 0;
    
    // ตั้งค่ารูปภาพเริ่มต้นเป็นรูปเดิมก่อน
    $imgPath = $product['image'];

    // จัดการอัปโหลดรูปภาพใหม่ (ถ้ามีการเลือกไฟล์ใหม่)
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
                // สร้าง path ใหม่
                $imgPath = '/shoestore/uploads/' . $filename;
                
                // ลบรูปเก่าทิ้งเพื่อประหยัดพื้นที่เซิร์ฟเวอร์ (ถ้ามีรูปเก่าและไม่ใช่ placeholder)
                if(!empty($product['image'])){
                    $old_file = __DIR__ . preg_replace('#^/shoestore#', '', $product['image']);
                    if(file_exists($old_file)) @unlink($old_file);
                }
            } else {
                $error = 'ไม่สามารถอัปโหลดรูปภาพใหม่ได้';
            }
        } else {
            $error = 'ชนิดไฟล์ไม่รองรับ (jpg, png, gif, webp)';
        }
    }

    // อัปเดตข้อมูลลงฐานข้อมูล
    if(!$name || $price <= 0){
        $error = $error ?: 'โปรดกรอกชื่อและราคาให้ถูกต้อง';
    } else {
        $update_stmt = $conn->prepare('UPDATE products SET name=?, description=?, price=?, image=?, category=?, is_popular=? WHERE id=?');
        $update_stmt->bind_param('ssdssii', $name, $desc, $price, $imgPath, $category, $is_popular, $edit_id);
        
        if($update_stmt->execute()){ 
            $_SESSION['msg'] = 'อัปเดตข้อมูลสินค้าเรียบร้อยแล้ว';
            // รีเฟรชหน้าเพื่อดึงข้อมูลใหม่มาโชว์
            header("Location: edit_product.php?id=" . $edit_id);
            exit;
        } else { 
            $error = 'Error: '.$conn->error; 
        }
    }
}

// รับข้อความแจ้งเตือน (หลังจากการรีเฟรชหน้า)
if(isset($_SESSION['msg'])) { $msg = $_SESSION['msg']; unset($_SESSION['msg']); }

include_once __DIR__.'/header.php';
?>

<style>
    .admin-card {
        background: var(--card, #fff);
        padding: 30px;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.04);
        margin-bottom: 40px;
    }
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
    @media (max-width: 768px) { .form-grid { grid-template-columns: 1fr; } }

    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; font-weight: bold; color: var(--dark); margin-bottom: 8px; }
    .form-control { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-family: inherit; outline: none; transition: border-color 0.2s; }
    .form-control:focus { border-color: var(--primary); }
    
    .file-upload-box { border: 2px dashed #ddd; padding: 20px; border-radius: 8px; background: #fcfcfc; text-align: center; }
    .current-image { width: 120px; height: 120px; object-fit: contain; border-radius: 8px; margin-bottom: 10px; border: 1px solid #eee; background: #fff; padding: 5px; }

    .checkbox-container { display: flex; align-items: center; gap: 10px; background: #fffdf5; border: 1px solid #ffeaa7; padding: 12px; border-radius: 8px; cursor: pointer; }
    .checkbox-container input { width: 18px; height: 18px; cursor: pointer; }

    .btn-submit { background: #f39c12; color: white; border: none; padding: 12px 30px; font-size: 16px; font-weight: bold; border-radius: 8px; cursor: pointer; transition: transform 0.2s; }
    .btn-submit:hover { transform: translateY(-2px); background: #e67e22; }
    .btn-cancel { background: #f4f6f8; color: #333; text-decoration: none; padding: 12px 30px; font-size: 16px; font-weight: bold; border-radius: 8px; display: inline-block; margin-right: 10px; transition: background 0.2s; }
    .btn-cancel:hover { background: #e2e6ea; }

    .alert { padding: 15px 20px; border-radius: 12px; margin-bottom: 20px; font-weight: bold; }
    .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
</style>

<main style="max-width: 1000px; margin: 0 auto; padding: 20px;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="margin: 0; color: var(--dark); font-size: 26px;">✏️ แก้ไขข้อมูลสินค้า</h2>
        <a href="admin.php" style="color: var(--muted); text-decoration: none; font-weight: bold;">← กลับหน้าจัดการสินค้า</a>
    </div>

    <?php if(!empty($msg)) echo '<div class="alert alert-success">✅ '.htmlspecialchars($msg).'</div>'; ?>
    <?php if(!empty($error)) echo '<div class="alert alert-error">❌ '.htmlspecialchars($error).'</div>'; ?>

    <div class="admin-card">
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="edit">
            
            <div class="form-grid">
                <div>
                    <div class="form-group">
                        <label>ชื่อสินค้า <span style="color:red;">*</span></label>
                        <input type="text" name="name" class="form-control" required value="<?php echo htmlspecialchars($product['name']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>ราคา (บาท) <span style="color:red;">*</span></label>
                        <input type="number" name="price" class="form-control" step="0.01" required value="<?php echo htmlspecialchars($product['price']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>หมวดหมู่สินค้า</label>
                        <select name="category" class="form-control">
                            <?php $cat = $product['category'] ?? 'Sneakers'; ?>
                            <option value="Sneakers" <?php echo $cat === 'Sneakers' ? 'selected' : ''; ?>>👟 รองเท้าสตรีทแวร์ (Sneakers)</option>
                            <option value="Running" <?php echo $cat === 'Running' ? 'selected' : ''; ?>>🏃‍♂️ รองเท้าวิ่ง (Running)</option>
                            <option value="Sandals" <?php echo $cat === 'Sandals' ? 'selected' : ''; ?>>🩴 รองเท้าแตะ (Sandals)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>คำอธิบายสินค้า</label>
                        <textarea name="description" class="form-control" rows="5"><?php echo htmlspecialchars($product['description']); ?></textarea>
                    </div>
                </div>

                <div>
                    <div class="form-group">
                        <label>รูปภาพสินค้า</label>
                        <div class="file-upload-box">
                            <img src="<?php echo htmlspecialchars($product['image'] ?: 'placeholder.png'); ?>" class="current-image" alt="Current Image">
                            <p style="margin: 0 0 10px 0; font-size: 14px; color: var(--muted);">รูปภาพปัจจุบัน</p>
                            
                            <input type="file" name="image" accept="image/*" style="width: 100%; border: 1px solid #ddd; padding: 8px; border-radius: 6px; background: #fff;">
                            <small style="color: #e74c3c; display: block; margin-top: 8px;">* หากไม่ต้องการเปลี่ยนรูปภาพ ไม่ต้องเลือกไฟล์ใดๆ</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div style="margin-top: 30px; border-top: 2px solid #eee; padding-top: 20px; text-align: right;">
                <a href="admin.php" class="btn-cancel">ยกเลิก</a>
                <button type="submit" class="btn-submit">💾 บันทึกการเปลี่ยนแปลง</button>
            </div>
        </form>
    </div>

</main>

<?php include_once __DIR__.'/footer.php'; ?>