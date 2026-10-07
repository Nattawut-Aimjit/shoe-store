<?php 
if(session_status() === PHP_SESSION_NONE) { session_start(); }
include_once __DIR__.'/db.php';

// 1. เช็คว่าล็อกอินหรือยัง
if(!isset($_SESSION['user'])){ 
    header('Location: login.php');
    exit;
}

// 2. เช็คว่ามีสินค้าในตะกร้าหรือไม่
if(empty($_SESSION['cart'])){
    header('Location: cart.php');
    exit;
}

$user_id = $_SESSION['user']['id'];
$cart = $_SESSION['cart'];
$ids = array_keys($cart);
$error = '';

// ================= 3. เมื่อกดปุ่ม "ยืนยันการสั่งซื้อ" =================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'place_order') {
    
    // รับค่าที่อยู่จัดส่งจากฟอร์ม
    $shipping_name = trim($_POST['shipping_name'] ?? '');
    $shipping_phone = trim($_POST['shipping_phone'] ?? '');
    $shipping_address = trim($_POST['shipping_address'] ?? '');

    if(empty($shipping_name) || empty($shipping_phone) || empty($shipping_address)){
        $error = "กรุณากรอกข้อมูลการจัดส่งให้ครบถ้วน";
    } else {
        // ดึงราคาสินค้าเพื่อคำนวณยอดรวม (ป้องกันการแฮ็กราคา)
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $types = str_repeat('i', count($ids));
        $stmt = $conn->prepare("SELECT id, price FROM products WHERE id IN ($placeholders)");
        $stmt->bind_param($types, ...$ids);
        $stmt->execute();
        $res = $stmt->get_result();

        $products = [];
        $total_amount = 0.0;
        while($row = $res->fetch_assoc()){
            $products[$row['id']] = $row['price'];
            $total_amount += $row['price'] * $cart[$row['id']];
        }

        // เริ่ม Transaction บันทึกข้อมูล
        $conn->begin_transaction();
        try {
            // 3.1 สร้างใบสั่งซื้อหลัก พร้อมบันทึกที่อยู่ลงไปด้วย (สำคัญมาก)
            $stmt_order = $conn->prepare("INSERT INTO orders (user_id, total_amount, status, shipping_name, shipping_phone, shipping_address) VALUES (?, ?, 'pending', ?, ?, ?)");
            $stmt_order->bind_param("idsss", $user_id, $total_amount, $shipping_name, $shipping_phone, $shipping_address);
            $stmt_order->execute();
            $order_id = $conn->insert_id;

            // 3.2 บันทึกรายการสินค้าลง order_items
            $stmt_item = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            foreach($cart as $product_id => $quantity){
                if(isset($products[$product_id])){
                    $price = $products[$product_id];
                    $stmt_item->bind_param("iiid", $order_id, $product_id, $quantity, $price);
                    $stmt_item->execute();
                }
            }

            // 3.3 ยืนยันการบันทึก
            $conn->commit();

            // ล้างตะกร้า แล้วเด้งไปหน้าชำระเงินจำลอง
            unset($_SESSION['cart']);
            header("Location: payment.php?order_id=" . $order_id);
            exit;

        } catch (Exception $e) {
            $conn->rollback();
            $error = "เกิดข้อผิดพลาดในการสั่งซื้อ: " . $e->getMessage();
        }
    }
}

// ================= 4. ดึงข้อมูลเพื่อแสดงผลหน้าเว็บ (ฝั่ง User Interface) =================

// ดึงข้อมูลโปรไฟล์ของลูกค้ามาตั้งเป็นค่าเริ่มต้นในฟอร์มที่อยู่
$stmt_user = $conn->prepare("SELECT full_name, phone, address FROM users WHERE id = ?");
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user_profile = $stmt_user->get_result()->fetch_assoc();

// ดึงข้อมูลสินค้าในตะกร้ามาแสดงสรุปบิล
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$types = str_repeat('i', count($ids));
$stmt_cart = $conn->prepare("SELECT id, name, price, image FROM products WHERE id IN ($placeholders)");
$stmt_cart->bind_param($types, ...$ids);
$stmt_cart->execute();
$res_cart = $stmt_cart->get_result();

$cart_items = [];
$display_total = 0;
while($row = $res_cart->fetch_assoc()) {
    $row['qty'] = $cart[$row['id']];
    $display_total += ($row['price'] * $row['qty']);
    $cart_items[] = $row;
}

include_once __DIR__.'/header.php';
?>

<style>
    .checkout-wrapper {
        display: flex;
        flex-wrap: wrap;
        gap: 30px;
        margin-top: 20px;
        margin-bottom: 50px;
    }
    
    .checkout-form-container {
        flex: 2;
        min-width: 300px;
        background: var(--card, #fff);
        padding: 30px;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.04);
    }
    
    .checkout-summary {
        flex: 1;
        min-width: 300px;
        background: #f8f9fa;
        padding: 30px;
        border-radius: 16px;
        height: fit-content;
        position: sticky;
        top: 20px;
        border: 1px solid #eee;
    }

    .form-group { margin-bottom: 20px; }
    .form-group label {
        display: block; font-weight: bold; color: var(--dark); margin-bottom: 8px; font-size: 15px;
    }
    .form-control {
        width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px;
        font-size: 15px; font-family: inherit; outline: none; transition: border-color 0.2s;
    }
    .form-control:focus { border-color: var(--primary); }

    /* ตารางสรุปสินค้า */
    .summary-item { display: flex; align-items: center; gap: 15px; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px dashed #ddd; }
    .summary-img { width: 60px; height: 60px; object-fit: contain; background: #fff; border-radius: 8px; padding: 5px; border: 1px solid #eee; }
    .summary-details { flex: 1; }
    .summary-name { font-weight: bold; font-size: 14px; color: var(--dark); margin-bottom: 5px; }
    .summary-qty { color: var(--muted); font-size: 13px; }
    .summary-price { font-weight: bold; color: var(--primary); }

    .summary-total-row { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 15px; color: #555; }
    .summary-net { display: flex; justify-content: space-between; margin-top: 15px; padding-top: 15px; border-top: 2px solid #ddd; font-size: 20px; font-weight: bold; color: #e74c3c; }

    .btn-checkout {
        background: #27ae60; color: white; border: none; width: 100%; padding: 15px;
        border-radius: 8px; font-size: 18px; font-weight: bold; cursor: pointer;
        transition: transform 0.2s; margin-top: 20px; display: flex; justify-content: center; gap: 10px;
    }
    .btn-checkout:hover { background: #219653; transform: translateY(-2px); }

    .alert-error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: bold; text-align: center; border: 1px solid #f5c6cb; }
</style>

<main style="max-width: 1100px; margin: 0 auto; padding: 20px;">
    
    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px;">
        <h2 style="margin: 0; font-size: 28px; color: var(--dark);">💳 ดำเนินการสั่งซื้อ (Checkout)</h2>
    </div>

    <?php if(!empty($error)) echo '<div class="alert-error">❌ '.htmlspecialchars($error).'</div>'; ?>

    <form method="post" class="checkout-wrapper">
        <input type="hidden" name="action" value="place_order">
        
        <div class="checkout-form-container">
            <h3 style="margin-top: 0; border-bottom: 2px solid #eee; padding-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                📍 ที่อยู่สำหรับจัดส่งพัสดุ
            </h3>
            
            <p style="color: var(--muted); font-size: 14px; margin-bottom: 20px;">
                * ข้อมูลนี้ถูกดึงมาจากโปรไฟล์ของคุณ คุณสามารถแก้ไขได้เฉพาะสำหรับออเดอร์นี้โดยไม่กระทบกับโปรไฟล์หลัก
            </p>

            <div class="form-group">
                <label>ชื่อ-นามสกุล (ผู้รับ)</label>
                <input type="text" name="shipping_name" class="form-control" required 
                       value="<?php echo htmlspecialchars($_POST['shipping_name'] ?? $user_profile['full_name'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label>เบอร์โทรศัพท์ติดต่อ</label>
                <input type="text" name="shipping_phone" class="form-control" required 
                       value="<?php echo htmlspecialchars($_POST['shipping_phone'] ?? $user_profile['phone'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label>ที่อยู่จัดส่งแบบเต็ม</label>
                <textarea name="shipping_address" class="form-control" rows="4" required><?php echo htmlspecialchars($_POST['shipping_address'] ?? $user_profile['address'] ?? ''); ?></textarea>
            </div>
        </div>

        <div class="checkout-summary">
            <h3 style="margin-top: 0; border-bottom: 2px solid #ddd; padding-bottom: 15px;">🧾 สรุปคำสั่งซื้อ</h3>
            
            <div style="max-height: 300px; overflow-y: auto; padding-right: 10px; margin-bottom: 20px;">
                <?php foreach($cart_items as $item): ?>
                    <div class="summary-item">
                        <img src="<?php echo htmlspecialchars($item['image'] ?: 'placeholder.png'); ?>" class="summary-img">
                        <div class="summary-details">
                            <div class="summary-name"><?php echo htmlspecialchars($item['name']); ?></div>
                            <div class="summary-qty">จำนวน: <?php echo $item['qty']; ?> ชิ้น</div>
                        </div>
                        <div class="summary-price">฿ <?php echo number_format($item['price'] * $item['qty'], 2); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="summary-total-row">
                <span>ยอดรวมสินค้า</span>
                <span>฿ <?php echo number_format($display_total, 2); ?></span>
            </div>
            <div class="summary-total-row">
                <span>ค่าจัดส่ง</span>
                <span style="color: #27ae60; font-weight: bold;">จัดส่งฟรี</span>
            </div>

            <div class="summary-net">
                <span>ยอดสุทธิ</span>
                <span>฿ <?php echo number_format($display_total, 2); ?></span>
            </div>

            <button type="submit" class="btn-checkout">
                ยืนยันคำสั่งซื้อ ➔
            </button>
            <div style="text-align: center; margin-top: 15px;">
                <a href="cart.php" style="color: var(--muted); text-decoration: none; font-size: 14px;">← กลับไปแก้ไขตะกร้า</a>
            </div>
        </div>
    </form>

</main>

<?php include_once __DIR__.'/footer.php'; ?>