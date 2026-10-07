<?php 
include_once __DIR__.'/db.php';

// บังคับให้ล็อกอินก่อนเข้าหน้านี้
if(!is_logged_in()){
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user']['id'];

// ดึงข้อมูลใบสั่งซื้อของ user คนนี้ เรียงจากล่าสุดไปเก่าสุด
$stmt = $conn->prepare("SELECT id, total_amount, status, created_at FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();

include_once __DIR__.'/header.php';
?>

<style>
    /* ดีไซน์กล่องครอบตาราง */
    .orders-container {
        background: var(--card, #ffffff);
        padding: 30px;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        margin-top: 20px;
        margin-bottom: 50px;
        overflow-x: auto;
    }

    /* ดีไซน์ตาราง */
    .order-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 700px; 
    }

    .order-table th {
        background: #f8f9fa;
        color: #555;
        padding: 16px;
        text-align: left;
        font-weight: 600;
        border-bottom: 2px solid #eee;
        white-space: nowrap;
    }

    .order-table td {
        padding: 16px;
        border-bottom: 1px solid #f0f0f0;
        color: #333;
        vertical-align: middle;
    }

    .order-table tbody tr:hover {
        background: #fafafa;
    }

    /* ดีไซน์หมายเลขออเดอร์ให้กดได้และดูสวย */
    .order-number {
        font-weight: bold;
        color: var(--primary, #1f6feb);
        text-decoration: none;
        background: #f4f6f8;
        padding: 6px 12px;
        border-radius: 8px;
        font-family: 'Courier New', Courier, monospace; /* ใช้ฟอนต์แบบใบเสร็จ */
        transition: all 0.2s;
        display: inline-block;
    }
    .order-number:hover {
        background: var(--primary, #1f6feb);
        color: #fff;
    }

    /* 🌟 ป้ายสถานะ (Badges) */
    .status-badge {
        display: inline-block;
        padding: 6px 14px;
        border-radius: 50px;
        font-size: 13px;
        font-weight: bold;
        text-align: center;
        white-space: nowrap;
    }
    .status-pending { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
    .status-paid { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .status-shipped { background: #cce5ff; color: #004085; border: 1px solid #b8daff; }
    .status-cancelled { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

    /* หน้าจอตอนไม่มีออเดอร์ */
    .empty-orders {
        text-align: center;
        padding: 50px 20px;
    }
</style>

<main style="max-width: 1100px; margin: 0 auto; padding: 20px;">
    <h2 style="margin-bottom: 20px; color: var(--dark, #222); font-size: 26px;">📦 ประวัติการสั่งซื้อของฉัน</h2>

    <div class="orders-container">
        <?php if($res->num_rows === 0): ?>
            <div class="empty-orders">
                <div style="font-size: 60px; margin-bottom: 15px;">🛍️</div>
                <h3 style="color: var(--dark, #222); margin-top: 0; font-size: 22px;">คุณยังไม่มีประวัติการสั่งซื้อ</h3>
                <p style="color: var(--muted, #666); margin-bottom: 25px;">แวะไปดูสินค้าใหม่ๆ ของเราก่อนสิครับ รับรองว่าต้องมีคู่ที่ถูกใจแน่นอน!</p>
                <a href="products.php" class="btn" style="padding: 12px 30px; font-size: 16px; text-decoration: none;">ไปช้อปปิ้งกันเลย</a>
            </div>
        <?php else: ?>
            <table class="order-table">
                <thead>
                    <tr>
                        <th>หมายเลขออเดอร์</th>
                        <th>วันที่สั่งซื้อ</th>
                        <th>ยอดรวม (บาท)</th>
                        <th>สถานะ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $res->fetch_assoc()): 
                        // 🌟 สร้างเลข Order ให้ดูสมจริง เช่น ORD-20260320-0001
                        $date_str = date('Ymd', strtotime($row['created_at'])); // ดึงแค่ ปีเดือนวัน
                        // เติมเลข 0 ข้างหน้า ID ให้ครบ 4 หลักด้วย sprintf
                        $realistic_order_id = sprintf("ORD-%s-%04d", $date_str, $row['id']);
                    ?>
                        <tr>
                            <td>
                                <a href="order_details.php?id=<?php echo $row['id']; ?>" class="order-number">
                                    <?php echo $realistic_order_id; ?>
                                </a>
                            </td>
                            
                            <td style="color: #666;">
                                <?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?>
                            </td>
                            
                            <td style="font-weight: bold; font-size: 16px;">
                                ฿ <?php echo number_format($row['total_amount'], 2); ?>
                            </td>
                            
                            <td>
                                <?php 
                                    $status = $row['status'];
                                    if($status === 'pending') {
                                        echo '<span class="status-badge status-pending">⏳ รอดำเนินการ</span>';
                                    } elseif($status === 'paid') {
                                        echo '<span class="status-badge status-paid">✅ ชำระเงินแล้ว</span>';
                                    } elseif($status === 'shipped') {
                                        echo '<span class="status-badge status-shipped">🚚 จัดส่งแล้ว</span>';
                                    } elseif($status === 'cancelled') {
                                        echo '<span class="status-badge status-cancelled">❌ ยกเลิก</span>';
                                    }
                                ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</main>

<?php include_once __DIR__.'/footer.php'; ?>