<?php
if(session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__.'/db.php';

// ป้องกันคนที่ไม่ใช่แอดมิน
if(!is_admin()){ header('HTTP/1.1 403 Forbidden'); echo 'ต้องเป็นแอดมินเท่านั้น'; exit; }

// ดึงข้อมูลลูกค้า พร้อมคำนวณยอดออเดอร์และยอดซื้อสะสม (เฉพาะบิลที่จ่ายเงิน/จัดส่งแล้ว)
$sql = "
    SELECT 
        u.id, 
        u.username, 
        u.email, 
        COUNT(o.id) as total_orders, 
        COALESCE(SUM(o.total_amount), 0) as total_spent
    FROM users u
    LEFT JOIN orders o ON u.id = o.user_id AND o.status IN ('paid', 'shipped')
    WHERE u.username != 'admin'
    GROUP BY u.id
    ORDER BY total_spent DESC, total_orders DESC
";
$result = $conn->query($sql);

include_once __DIR__.'/header.php';
?>

<style>
    .admin-container {
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
        min-width: 800px;
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

    /* จัดอันดับด้วยตัวเลข */
    .rank-number {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        background: #eee;
        border-radius: 50%;
        font-weight: bold;
        color: #555;
    }
    .rank-1 { background: #f1c40f; color: white; box-shadow: 0 2px 10px rgba(241,196,15,0.4); }
    .rank-2 { background: #bdc3c7; color: white; }
    .rank-3 { background: #cd7f32; color: white; }

    /* ปุ่มดูประวัติ */
    .btn-view {
        background: #f4f6f8;
        color: var(--primary);
        text-decoration: none;
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: bold;
        transition: all 0.2s;
        display: inline-block;
    }
    .btn-view:hover {
        background: var(--primary);
        color: #fff;
    }
</style>

<main style="max-width: 1200px; margin: 0 auto; padding: 20px;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="margin: 0; color: var(--dark); font-size: 28px;">👥 ข้อมูลสมาชิกลูกค้า (Customers)</h2>
        <a href="admin_dashboard.php" style="color: var(--muted); text-decoration: none; font-weight: bold;">← กลับหน้าแดชบอร์ด</a>
    </div>

    <div class="admin-container">
        
        <table class="admin-table">
            <thead>
                <tr>
                    <th style="text-align: center; width: 80px;">อันดับ</th>
                    <th>ชื่อลูกค้า</th>
                    <th>อีเมลติดต่อ</th>
                    <th style="text-align: center;">จำนวนออเดอร์</th>
                    <th style="text-align: right;">ยอดซื้อสะสม (บาท)</th>
                    <th style="text-align: center;">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php if($result->num_rows > 0): ?>
                    <?php $rank = 1; while($row = $result->fetch_assoc()): 
                        $spent = $row['total_spent'];
                    ?>
                        <tr>
                            <td style="text-align: center;">
                                <span class="rank-number <?php echo $rank <= 3 && $spent > 0 ? 'rank-'.$rank : ''; ?>">
                                    <?php echo $rank; ?>
                                </span>
                            </td>
                            
                            <td style="font-weight: bold; color: var(--dark);">
                                👤 <?php echo htmlspecialchars($row['username']); ?>
                            </td>
                            
                            <td style="color: #666;">
                                📧 <?php echo htmlspecialchars($row['email']); ?>
                            </td>
                            
                            <td style="text-align: center; font-size: 16px;">
                                <?php echo number_format($row['total_orders']); ?> บิล
                            </td>
                            
                            <td style="text-align: right; font-weight: bold; font-size: 16px; color: var(--primary);">
                                ฿ <?php echo number_format($spent, 2); ?>
                            </td>
                            
                            <td style="text-align: center;">
                                <a href="admin_orders.php?search=<?php echo urlencode($row['username']); ?>" class="btn-view">
                                    🔍 ดูประวัติ
                                </a>
                            </td>
                        </tr>
                    <?php $rank++; endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px; color: var(--muted);">
                            ยังไม่มีสมาชิกลูกค้าในระบบ
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

    </div>
</main>

<?php include_once __DIR__.'/footer.php'; ?>