<?php 
if(session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__.'/db.php';

// ตรวจสอบว่าล็อกอินหรือยัง
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$is_admin_user = is_admin(); // เช็คสถานะแอดมิน

// ตรวจสอบว่ามีการส่ง ID ออเดอร์มาหรือไม่
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $redirect = $is_admin_user ? 'admin_orders.php' : 'my_orders.php';
    echo "<script>alert('ไม่พบข้อมูลออเดอร์'); window.location='$redirect';</script>";
    exit;
}

$order_id = intval($_GET['id']);
$user_id = $_SESSION['user']['id'];

// ================= 1. ดึงข้อมูลออเดอร์ =================
if ($is_admin_user) {
    // 🌟 ถ้าเป็นแอดมิน: ดึงข้อมูลได้เลย ไม่ต้องเช็คว่าใครเป็นคนสั่ง
    $stmt = $conn->prepare("
        SELECT o.*, u.username, u.email 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        WHERE o.id = ?
    ");
    $stmt->bind_param("i", $order_id);
} else {
    // 👤 ถ้าเป็นลูกค้า: ต้องเช็คด้วยว่า user_id ตรงกับคนที่ล็อกอินอยู่ไหม
    $stmt = $conn->prepare("
        SELECT o.*, u.username, u.email 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        WHERE o.id = ? AND o.user_id = ?
    ");
    $stmt->bind_param("ii", $order_id, $user_id);
}

$stmt->execute();
$order_result = $stmt->get_result();

if ($order_result->num_rows === 0) {
    $redirect = $is_admin_user ? 'admin_orders.php' : 'my_orders.php';
    echo "<script>alert('ไม่พบออเดอร์นี้ หรือคุณไม่มีสิทธิ์เข้าถึง'); window.location='$redirect';</script>";
    exit;
}
$order = $order_result->fetch_assoc();

// 2. แปลงเลข Order ให้ดูสมจริง
$date_str = date('Ymd', strtotime($order['created_at']));
$realistic_order_id = sprintf("ORD-%s-%04d", $date_str, $order['id']);

// 3. ดึงรายการสินค้าในออเดอร์นี้
$stmt_items = $conn->prepare("
    SELECT oi.*, p.name, p.image 
    FROM order_items oi 
    LEFT JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = ?
");
$stmt_items->bind_param("i", $order_id);
$stmt_items->execute();
$items_result = $stmt_items->get_result();

// กำหนดปุ่ม "ย้อนกลับ" ให้กลับไปถูกหน้าตามประเภทของ User
$back_link = $is_admin_user ? 'admin_orders.php' : 'my_orders.php';
$back_text = $is_admin_user ? '⬅ กลับไปหน้าจัดการออเดอร์' : '⬅ กลับไปประวัติการสั่งซื้อ';

include_once __DIR__.'/header.php';
?>

<style>
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px; }
    .btn-back { background: #f4f6f8; color: #333; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: bold; transition: all 0.2s; display: inline-flex; align-items: center; gap: 8px; }
    .btn-back:hover { background: #e2e6ea; }
    .info-card { background: var(--card, #fff); padding: 30px; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); margin-bottom: 30px; }
    .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; }
    .info-item label { display: block; color: var(--muted, #666); font-size: 14px; margin-bottom: 5px; font-weight: 600; }
    .info-item span { font-size: 16px; color: var(--dark, #222); font-weight: bold; }
    
    .items-table { width: 100%; border-collapse: collapse; min-width: 600px; }
    .items-table th { background: #f8f9fa; color: #555; padding: 16px; text-align: left; font-weight: 600; border-bottom: 2px solid #eee; }
    .items-table td { padding: 16px; border-bottom: 1px solid #f0f0f0; vertical-align: middle; }
    .item-img { width: 60px; height: 60px; object-fit: contain; background: #f8f9fa; border-radius: 8px; padding: 4px; border: 1px solid #eee; }
    
    .total-section { margin-top: 20px; padding-top: 20px; border-top: 2px dashed #eee; display: flex; justify-content: flex-end; align-items: center; gap: 20px; }
    .status-badge { display: inline-block; padding: 6px 14px; border-radius: 50px; font-size: 13px; font-weight: bold; text-align: center; }
    .status-pending { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
    .status-paid { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .status-shipped { background: #cce5ff; color: #004085; border: 1px solid #b8daff; }
    .status-cancelled { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

    .shipping-box { background: #fdfdfd; border: 1px solid #eee; padding: 20px; border-radius: 12px; margin-top: 20px; }
    .shipping-title { margin: 0 0 10px 0; font-size: 16px; color: var(--dark); display: flex; align-items: center; gap: 8px; }
</style>

<main style="max-width: 1100px; margin: 0 auto; padding: 20px;">
    
    <div class="page-header">
        <h2 style="margin: 0; color: var(--dark, #222); font-size: 26px;">
            📄 รายละเอียดคำสั่งซื้อ <span style="color: var(--primary); font-family: monospace;"><?php echo $realistic_order_id; ?></span>
        </h2>
        <a href="<?php echo $back_link; ?>" class="btn-back"><?php echo $back_text; ?></a>
    </div>

    <div class="info-card">
        <div class="info-grid">
            <div class="info-item">
                <label>บัญชีผู้สั่งซื้อ:</label>
                <span><?php echo htmlspecialchars($order['username']); ?> (<?php echo htmlspecialchars($order['email']); ?>)</span>
            </div>
            <div class="info-item">
                <label>วันที่สั่งซื้อ:</label>
                <span><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?> น.</span>
            </div>
            <div class="info-item">
                <label>สถานะออเดอร์:</label>
                <?php 
                    $status = $order['status'];
                    if($status === 'pending') echo '<span class="status-badge status-pending">⏳ รอดำเนินการ</span>';
                    elseif($status === 'paid') echo '<span class="status-badge status-paid">✅ ชำระเงินแล้ว</span>';
                    elseif($status === 'shipped') echo '<span class="status-badge status-shipped">🚚 จัดส่งแล้ว</span>';
                    elseif($status === 'cancelled') echo '<span class="status-badge status-cancelled">❌ ยกเลิก</span>';
                ?>
            </div>
        </div>

        <div class="shipping-box">
            <h4 class="shipping-title">📍 ข้อมูลสำหรับการจัดส่งพัสดุ</h4>
            <div style="color: #444; font-size: 15px; line-height: 1.6;">
                <strong>ผู้รับ:</strong> <?php echo htmlspecialchars($order['shipping_name'] ?? 'ไม่ได้ระบุ'); ?> 
                <span style="margin-left: 15px;"><strong>เบอร์โทร:</strong> <?php echo htmlspecialchars($order['shipping_phone'] ?? 'ไม่ได้ระบุ'); ?></span>
                <br>
                <div style="margin-top: 8px;">
                    <strong>ที่อยู่จัดส่ง:</strong><br>
                    <?php echo nl2br(htmlspecialchars($order['shipping_address'] ?? 'ไม่ได้ระบุที่อยู่')); ?>
                </div>
            </div>
        </div>
    </div>

    <div class="info-card" style="overflow-x: auto; padding: 0;">
        <div style="padding: 20px 30px; border-bottom: 2px solid #eee;">
            <h3 style="margin: 0; font-size: 20px;">🛍️ รายการสินค้า</h3>
        </div>
        
        <table class="items-table">
            <thead>
                <tr>
                    <th style="padding-left: 30px;">รูปสินค้า</th>
                    <th>ชื่อสินค้า</th>
                    <th>ราคาต่อชิ้น</th>
                    <th style="text-align: center;">จำนวน</th>
                    <th style="text-align: right; padding-right: 30px;">รวม</th>
                </tr>
            </thead>
            <tbody>
                <?php while($item = $items_result->fetch_assoc()): 
                    $subtotal = $item['price'] * $item['quantity'];
                ?>
                <tr>
                    <td style="padding-left: 30px;">
                        <img src="<?php echo htmlspecialchars($item['image'] ?: 'placeholder.png'); ?>" class="item-img" alt="Product">
                    </td>
                    <td style="font-weight: 600;">
                        <?php echo htmlspecialchars($item['name'] ?? 'สินค้านี้ถูกลบไปแล้ว'); ?>
                    </td>
                    <td>฿ <?php echo number_format($item['price'], 2); ?></td>
                    <td style="text-align: center; font-weight: bold;"><?php echo htmlspecialchars($item['quantity']); ?></td>
                    <td style="text-align: right; padding-right: 30px; font-weight: bold; color: var(--primary);">
                        ฿ <?php echo number_format($subtotal, 2); ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div style="padding: 0 30px 30px 30px;">
            <div class="total-section">
                <span style="font-size: 18px; color: var(--muted);">ยอดชำระสุทธิ:</span>
                <span style="font-size: 28px; font-weight: bold; color: var(--primary);">
                    ฿ <?php echo number_format($order['total_amount'] ?? $order['total_price'], 2); ?>
                </span>
            </div>
        </div>
    </div>

</main>

<?php include_once __DIR__.'/footer.php'; ?>