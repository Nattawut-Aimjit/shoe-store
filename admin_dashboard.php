<?php
if(session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__.'/db.php';

// ตรวจสอบสิทธิ์ (ต้องเป็นแอดมินเท่านั้น)
// ปรับเงื่อนไขนี้ตามระบบของคุณ เช่น $_SESSION['user']['role'] == 'admin'
if (!isset($_SESSION['user']) || $_SESSION['user']['username'] !== 'admin') {
    echo "<script>alert('เฉพาะผู้ดูแลระบบเท่านั้น'); window.location='index.php';</script>";
    exit;
}

// ================= 1. ดึงข้อมูลสรุปยอด (Summary) =================
// ยอดขายรวมทั้งหมด (นับเฉพาะที่ชำระเงินแล้ว หรือ จัดส่งแล้ว)
$sales_stmt = $conn->query("SELECT SUM(total_amount) as total_sales FROM orders WHERE status IN ('paid', 'shipped')");
$total_sales = $sales_stmt->fetch_assoc()['total_sales'] ?? 0;

// ออเดอร์ทั้งหมด
$orders_stmt = $conn->query("SELECT COUNT(id) as total_orders FROM orders");
$total_orders = $orders_stmt->fetch_assoc()['total_orders'] ?? 0;

// รอดำเนินการ
$pending_stmt = $conn->query("SELECT COUNT(id) as pending_orders FROM orders WHERE status = 'pending'");
$pending_orders = $pending_stmt->fetch_assoc()['pending_orders'] ?? 0;

// จำนวนสมาชิกลูกค้า
$users_stmt = $conn->query("SELECT COUNT(id) as total_users FROM users WHERE username != 'admin'");
$total_users = $users_stmt->fetch_assoc()['total_users'] ?? 0;

// ================= 2. ดึงข้อมูลออเดอร์ล่าสุด 5 รายการ =================
$latest_orders = $conn->query("
    SELECT o.id, o.total_amount, o.status, o.created_at, u.username 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC 
    LIMIT 5
");

include_once __DIR__.'/header.php';
?>

<style>
    /* ดีไซน์กล่อง Dashboard Cards */
    .dashboard-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
        margin-top: 20px;
        margin-bottom: 40px;
    }
    
    .stat-card {
        background: var(--card, #fff);
        padding: 25px 20px;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.04);
        text-align: center;
        border-bottom: 5px solid var(--primary);
        transition: transform 0.2s;
    }
    .stat-card:hover { transform: translateY(-5px); }
    
    .stat-card.sales { border-color: #1f6feb; }
    .stat-card.orders { border-color: #00bcd4; }
    .stat-card.pending { border-color: #f39c12; }
    .stat-card.users { border-color: #9b59b6; }

    .stat-icon {
        font-size: 32px;
        margin-bottom: 10px;
    }

    .stat-title {
        color: var(--muted, #666);
        font-size: 15px;
        margin-bottom: 10px;
        font-weight: 600;
    }

    .stat-value {
        font-size: 28px;
        font-weight: bold;
        color: var(--dark, #222);
    }
    
    /* สไตล์เฉพาะยอดขายให้สีเด่นขึ้น */
    .stat-card.sales .stat-value { color: #1f6feb; }
    .stat-card.pending .stat-value { color: #f39c12; }

    /* ดีไซน์ตาราง */
    .table-container {
        background: var(--card, #fff);
        padding: 30px;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.04);
        margin-bottom: 50px;
        overflow-x: auto;
    }

    .admin-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 700px;
    }

    .admin-table th {
        background: #f8f9fa;
        color: #555;
        padding: 16px;
        text-align: left;
        font-weight: 600;
        border-bottom: 2px solid #eee;
    }

    .admin-table td {
        padding: 16px;
        border-bottom: 1px solid #f0f0f0;
        vertical-align: middle;
    }

    .admin-table tbody tr:hover { background: #fafafa; }

    /* รหัสออเดอร์ให้กดได้ */
    .order-number {
        font-weight: bold;
        color: var(--primary, #1f6feb);
        text-decoration: none;
        background: #f4f6f8;
        padding: 6px 12px;
        border-radius: 8px;
        font-family: monospace;
        transition: all 0.2s;
    }
    .order-number:hover { background: var(--primary); color: #fff; }

    /* ป้ายสถานะ */
    .status-badge {
        display: inline-block;
        padding: 6px 14px;
        border-radius: 50px;
        font-size: 13px;
        font-weight: bold;
        text-align: center;
    }
    .status-pending { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
    .status-paid { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .status-shipped { background: #cce5ff; color: #004085; border: 1px solid #b8daff; }
    .status-cancelled { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

    .btn-view-all {
        display: block;
        text-align: center;
        background: #f4f6f8;
        color: var(--dark);
        padding: 12px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: bold;
        margin-top: 20px;
        transition: background 0.2s;
    }
    .btn-view-all:hover { background: #e2e6ea; }
</style>

<main style="max-width: 1200px; margin: 0 auto; padding: 20px;">
    
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 10px;">
        <h2 style="margin: 0; color: var(--dark); font-size: 26px;">📊 แดชบอร์ดสรุปยอดขาย</h2>
        <span style="color: var(--muted);">อัปเดตล่าสุด: <?php echo date('d/m/Y H:i'); ?></span>
    </div>

    <a href="admin_report.php" style="text-decoration: none; color: inherit;">
    <div class="stat-card sales" style="cursor: pointer; position: relative;">
        <div class="stat-icon">💰</div>
        <div class="stat-title">ยอดขายรวมทั้งหมด</div>
        <div class="stat-value">฿ <?php echo number_format($total_sales, 2); ?></div>
        <div style="font-size: 13px; color: var(--primary); margin-top: 10px; font-weight: bold;">📊 ดูรายงานยอดขาย ➔</div>
    </div>
    <a href="admin_orders.php?status=all" style="text-decoration: none; color: inherit;">
    <div class="stat-card orders" style="cursor: pointer;">
        <div class="stat-icon">📦</div>
        <div class="stat-title">ออเดอร์ทั้งหมด</div>
        <div class="stat-value"><?php echo number_format($total_orders); ?> รายการ</div>
        <div style="font-size: 13px; color: #00bcd4; margin-top: 10px; font-weight: bold;">ดูทั้งหมด ➔</div>
    </div>
</a>

<a href="admin_orders.php?status=pending" style="text-decoration: none; color: inherit;">
    <div class="stat-card pending" style="cursor: pointer;">
        <div class="stat-icon">⏳</div>
        <div class="stat-title">รอดำเนินการ</div>
        <div class="stat-value"><?php echo number_format($pending_orders); ?> รายการ</div>
        <div style="font-size: 13px; color: #f39c12; margin-top: 10px; font-weight: bold;">จัดการทันที ➔</div>
    </div>
</a>

<a href="admin_customers.php" style="text-decoration: none; color: inherit;">
    <div class="stat-card users" style="cursor: pointer;">
        <div class="stat-icon">👥</div>
        <div class="stat-title">จำนวนสมาชิกลูกค้า</div>
        <div class="stat-value"><?php echo number_format($total_users); ?> คน</div>
        <div style="font-size: 13px; color: #9b59b6; margin-top: 10px; font-weight: bold;">ดูข้อมูลลูกค้า ➔</div>
    </div>
</a>

    <div class="table-container">
        <h3 style="margin-top: 0; margin-bottom: 20px; color: var(--dark);">🆕 ออเดอร์ล่าสุด</h3>
        
        <?php if($latest_orders->num_rows > 0): ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ออเดอร์</th>
                        <th>ลูกค้า</th>
                        <th>ยอดรวม (บาท)</th>
                        <th>วันที่</th>
                        <th>สถานะ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $latest_orders->fetch_assoc()): 
                        // สร้างเลข Order สมจริง
                        $date_str = date('Ymd', strtotime($row['created_at']));
                        $realistic_order_id = sprintf("ORD-%s-%04d", $date_str, $row['id']);
                    ?>
                        <tr>
                            <td>
                                <a href="admin_orders.php?search=<?php echo $row['id']; ?>" class="order-number">
                                    <?php echo $realistic_order_id; ?>
                                </a>
                            </td>
                            <td style="font-weight: 500;">
                                👤 <?php echo htmlspecialchars($row['username']); ?>
                            </td>
                            <td style="font-weight: bold;">
                                ฿ <?php echo number_format($row['total_amount'], 2); ?>
                            </td>
                            <td style="color: #666;">
                                <?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?>
                            </td>
                            <td>
                                <?php 
                                    $status = $row['status'];
                                    if($status === 'pending') echo '<span class="status-badge status-pending">⏳ รอดำเนินการ</span>';
                                    elseif($status === 'paid') echo '<span class="status-badge status-paid">✅ ชำระเงินแล้ว</span>';
                                    elseif($status === 'shipped') echo '<span class="status-badge status-shipped">🚚 จัดส่งแล้ว</span>';
                                    elseif($status === 'cancelled') echo '<span class="status-badge status-cancelled">❌ ยกเลิก</span>';
                                ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align: center; color: var(--muted); padding: 20px;">ยังไม่มีรายการสั่งซื้อในระบบ</p>
        ?>
        <?php endif; ?>

        <a href="admin_orders.php" class="btn-view-all">ดูออเดอร์ทั้งหมด ➔</a>
    </div>

</main>

<?php include_once __DIR__.'/footer.php'; ?>