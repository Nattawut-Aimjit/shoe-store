<?php
if(session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__.'/db.php';

// ป้องกันคนที่ไม่ใช่แอดมิน
if(!is_admin()){ header('HTTP/1.1 403 Forbidden'); echo 'ต้องเป็นแอดมินเท่านั้น'; exit; }

// ================= คำสั่ง SQL สำหรับดึงข้อมูลสถิติ =================
// เราจะนับเฉพาะออเดอร์ที่ "ชำระเงินแล้ว (paid)" หรือ "จัดส่งแล้ว (shipped)" เท่านั้น

// 1. สินค้าขายดี 5 อันดับแรก (นับตามจำนวนชิ้นที่ขายได้)
$sql_best_sellers = "
    SELECT p.id, p.name, p.image, p.price, SUM(oi.quantity) as total_sold
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    JOIN products p ON oi.product_id = p.id
    WHERE o.status IN ('paid', 'shipped')
    GROUP BY p.id
    ORDER BY total_sold DESC
    LIMIT 5
";
$best_sellers = $conn->query($sql_best_sellers);

// 2. สินค้าที่ทำรายได้สูงสุด 5 อันดับ (นับตามจำนวนเงินรวม)
$sql_top_revenue = "
    SELECT p.id, p.name, p.image, SUM(oi.quantity * oi.price) as total_revenue
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    JOIN products p ON oi.product_id = p.id
    WHERE o.status IN ('paid', 'shipped')
    GROUP BY p.id
    ORDER BY total_revenue DESC
    LIMIT 5
";
$top_revenue = $conn->query($sql_top_revenue);

// 3. สัดส่วนยอดขายตามหมวดหมู่
$sql_category = "
    SELECT p.category, SUM(oi.quantity) as total_items, SUM(oi.quantity * oi.price) as total_revenue
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    JOIN products p ON oi.product_id = p.id
    WHERE o.status IN ('paid', 'shipped')
    GROUP BY p.category
    ORDER BY total_revenue DESC
";
$category_stats = $conn->query($sql_category);

include_once __DIR__.'/header.php';
?>

<style>
    .report-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 30px;
        margin-bottom: 40px;
    }
    @media (max-width: 900px) {
        .report-grid { grid-template-columns: 1fr; }
    }

    .report-card {
        background: var(--card, #fff);
        padding: 30px;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.04);
    }

    .report-title {
        margin-top: 0;
        margin-bottom: 20px;
        border-bottom: 2px solid #eee;
        padding-bottom: 15px;
        color: var(--dark);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* ตารางในรายงาน */
    .report-table {
        width: 100%;
        border-collapse: collapse;
    }
    .report-table th {
        background: #f8f9fa;
        color: #555;
        padding: 12px;
        text-align: left;
        font-weight: 600;
        font-size: 14px;
    }
    .report-table td {
        padding: 12px;
        border-bottom: 1px solid #f0f0f0;
        vertical-align: middle;
    }
    
    .product-cell {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    .product-img {
        width: 50px;
        height: 50px;
        object-fit: contain;
        background: #f8f9fa;
        border-radius: 8px;
        padding: 5px;
        border: 1px solid #eee;
    }
    .product-name {
        font-weight: bold;
        color: var(--dark);
        font-size: 14px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .rank-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        font-weight: bold;
        color: white;
        background: #bbb;
    }
    .rank-1 { background: #f1c40f; box-shadow: 0 2px 10px rgba(241,196,15,0.4); } /* ทอง */
    .rank-2 { background: #bdc3c7; } /* เงิน */
    .rank-3 { background: #cd7f32; } /* ทองแดง */
</style>

<main style="max-width: 1200px; margin: 0 auto; padding: 20px;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h2 style="margin: 0; color: var(--dark); font-size: 28px;">📈 รายงานและวิเคราะห์ยอดขาย</h2>
        <a href="admin_dashboard.php" style="color: var(--muted); text-decoration: none; font-weight: bold;">← กลับหน้าแดชบอร์ด</a>
    </div>

    <div class="report-grid">
        
        <div class="report-card">
            <h3 class="report-title">🏆 5 อันดับสินค้าขายดีที่สุด (จำนวนชิ้น)</h3>
            <?php if($best_sellers->num_rows > 0): ?>
                <table class="report-table">
                    <tr>
                        <th style="width: 50px; text-align: center;">อันดับ</th>
                        <th>สินค้า</th>
                        <th style="text-align: right;">จำนวนที่ขายได้</th>
                    </tr>
                    <?php $rank = 1; while($row = $best_sellers->fetch_assoc()): ?>
                    <tr>
                        <td style="text-align: center;">
                            <span class="rank-badge <?php echo $rank <= 3 ? 'rank-'.$rank : ''; ?>"><?php echo $rank; ?></span>
                        </td>
                        <td>
                            <div class="product-cell">
                                <img src="<?php echo htmlspecialchars($row['image'] ?: 'placeholder.png'); ?>" class="product-img">
                                <div class="product-name"><?php echo htmlspecialchars($row['name']); ?></div>
                            </div>
                        </td>
                        <td style="text-align: right; font-weight: bold; font-size: 18px; color: var(--primary);">
                            <?php echo number_format($row['total_sold']); ?> ชิ้น
                        </td>
                    </tr>
                    <?php $rank++; endwhile; ?>
                </table>
            <?php else: ?>
                <p style="text-align: center; color: var(--muted); padding: 20px;">ยังไม่มีข้อมูลการขาย</p>
            <?php endif; ?>
        </div>

        <div class="report-card">
            <h3 class="report-title">💰 5 อันดับสินค้าที่ทำรายได้สูงสุด</h3>
            <?php if($top_revenue->num_rows > 0): ?>
                <table class="report-table">
                    <tr>
                        <th style="width: 50px; text-align: center;">อันดับ</th>
                        <th>สินค้า</th>
                        <th style="text-align: right;">รายได้รวม</th>
                    </tr>
                    <?php $rank = 1; while($row = $top_revenue->fetch_assoc()): ?>
                    <tr>
                        <td style="text-align: center;">
                            <span class="rank-badge <?php echo $rank <= 3 ? 'rank-'.$rank : ''; ?>"><?php echo $rank; ?></span>
                        </td>
                        <td>
                            <div class="product-cell">
                                <img src="<?php echo htmlspecialchars($row['image'] ?: 'placeholder.png'); ?>" class="product-img">
                                <div class="product-name"><?php echo htmlspecialchars($row['name']); ?></div>
                            </div>
                        </td>
                        <td style="text-align: right; font-weight: bold; font-size: 16px; color: #27ae60;">
                            ฿ <?php echo number_format($row['total_revenue'], 2); ?>
                        </td>
                    </tr>
                    <?php $rank++; endwhile; ?>
                </table>
            <?php else: ?>
                <p style="text-align: center; color: var(--muted); padding: 20px;">ยังไม่มีข้อมูลการขาย</p>
            <?php endif; ?>
        </div>

    </div>

    <div class="report-card">
        <h3 class="report-title">📊 สัดส่วนยอดขายแยกตามหมวดหมู่ (Category Performance)</h3>
        <?php if($category_stats->num_rows > 0): ?>
            <table class="report-table">
                <tr>
                    <th>หมวดหมู่สินค้า</th>
                    <th style="text-align: center;">จำนวนที่ขายได้รวม</th>
                    <th style="text-align: right;">รายได้รวมทั้งหมด</th>
                </tr>
                <?php while($row = $category_stats->fetch_assoc()): 
                    $cat_name = $row['category'] ?: 'ไม่ได้ระบุหมวดหมู่';
                    // ใส่ Emoji ให้หมวดหมู่ดูน่ารักขึ้น
                    if($cat_name === 'Sneakers') $cat_name = '👟 รองเท้าสตรีทแวร์ (Sneakers)';
                    if($cat_name === 'Running') $cat_name = '🏃‍♂️ รองเท้าวิ่ง (Running)';
                    if($cat_name === 'Sandals') $cat_name = '🩴 รองเท้าแตะ (Sandals)';
                ?>
                <tr>
                    <td style="font-weight: bold; font-size: 16px;"><?php echo htmlspecialchars($cat_name); ?></td>
                    <td style="text-align: center; font-size: 16px;"><?php echo number_format($row['total_items']); ?> คู่</td>
                    <td style="text-align: right; font-weight: bold; font-size: 18px; color: var(--primary);">
                        ฿ <?php echo number_format($row['total_revenue'], 2); ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p style="text-align: center; color: var(--muted); padding: 20px;">ยังไม่มีข้อมูลการขายในแต่ละหมวดหมู่</p>
        <?php endif; ?>
    </div>

</main>

<?php include_once __DIR__.'/footer.php'; ?>