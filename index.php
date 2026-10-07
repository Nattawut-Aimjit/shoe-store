<?php 
if(session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__.'/db.php'; 
include_once __DIR__.'/header.php'; 
?>

<style>
    /* แบนเนอร์หน้าแรก (Hero Section) */
    .hero-section {
        background: linear-gradient(135deg, var(--primary, #1f6feb) 0%, #00bcd4 100%);
        color: white;
        padding: 60px 20px;
        text-align: center;
        border-radius: 20px;
        margin-bottom: 50px;
        box-shadow: 0 10px 30px rgba(31, 111, 235, 0.2);
    }
    .hero-title { font-size: 40px; font-weight: 900; margin: 0 0 15px 0; }
    .hero-subtitle { font-size: 18px; opacity: 0.9; margin-bottom: 30px; }
    .btn-hero { background: white; color: var(--primary); padding: 12px 30px; border-radius: 50px; font-size: 16px; font-weight: bold; text-decoration: none; transition: transform 0.2s; display: inline-block; }
    .btn-hero:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }

    /* หัวข้อแต่ละโซน */
    .section-title {
        display: flex; justify-content: space-between; align-items: flex-end;
        margin-bottom: 25px; border-bottom: 2px solid #eee; padding-bottom: 15px;
    }
    .section-title h2 { margin: 0; font-size: 24px; color: var(--dark); }
    .section-title a { color: var(--primary); text-decoration: none; font-weight: bold; font-size: 14px; }
    .section-title a:hover { text-decoration: underline; }

    /* ป้ายสินค้า */
    .badge-hot-realtime { position: absolute; top: 10px; right: 10px; background: #e74c3c; color: white; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: bold; box-shadow: 0 2px 5px rgba(231,76,60,0.3); z-index: 1; }
    .badge-new { position: absolute; top: 10px; left: 10px; background: #27ae60; color: white; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: bold; z-index: 1; }
    .sold-count { font-size: 12px; color: #e67e22; font-weight: bold; margin-top: 5px; }
</style>

<main style="max-width: 1200px; margin: 0 auto; padding: 20px;">

    <div class="hero-section">
        <h1 class="hero-title">ก้าวไปข้างหน้าด้วยรองเท้าคู่ใจ 👟</h1>
        <p class="hero-subtitle">ค้นพบสไตล์ที่ใช่ สำหรับทุกการเดินทางของคุณกับ ShoeStore</p>
        <a href="products.php" class="btn-hero">ช้อปเลยตอนนี้ ➔</a>
    </div>

    <div class="section-title">
        <h2>🔥 สินค้ายอดฮิต (ขายดีประจำร้าน)</h2>
        <a href="products.php">ดูสินค้าทั้งหมด ➔</a>
    </div>
    
    <div class="grid" style="margin-bottom: 50px;">
        <?php
        // 🚀 SQL อัจฉริยะ: ดึงสินค้า 4 อันดับที่มียอดสั่งซื้อสูงสุด (นับเฉพาะบิลที่จ่ายเงิน/จัดส่งแล้ว)
        // ถ้าไม่มีบิลเลย (total_sold เป็น 0) ก็จะเรียงตามวันที่เพิ่มสินค้าล่าสุดแทน
        $sql_hot = "
            SELECT p.id, p.name, p.price, p.image, p.category, 
                   COALESCE(SUM(oi.quantity), 0) as total_sold
            FROM products p
            LEFT JOIN order_items oi ON p.id = oi.product_id
            LEFT JOIN orders o ON oi.order_id = o.id AND o.status IN ('paid', 'shipped')
            GROUP BY p.id
            ORDER BY total_sold DESC, p.created_at DESC
            LIMIT 4
        ";
        $res_hot = $conn->query($sql_hot);

        while($row = $res_hot->fetch_assoc()):
            $is_really_hot = $row['total_sold'] > 0; // ถ้ายอดขาย > 0 ให้โชว์ป้ายว่าขายดี
        ?>
            <div class="product" style="position: relative; <?php echo $is_really_hot ? 'border: 1px solid #ffeaa7; background: #fffdf5;' : ''; ?>">
                
                <?php if($is_really_hot): ?>
                    <div class="badge-hot-realtime">🔥 HOT</div>
                <?php endif; ?>

                <a href="product_detail.php?id=<?php echo $row['id']; ?>" style="text-decoration: none; color: inherit;">
                    <img src="<?php echo htmlspecialchars($row['image'] ?: 'placeholder.png'); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                    <h3 style="transition: color 0.2s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='inherit'"><?php echo htmlspecialchars($row['name']); ?></h3>
                </a>
                
                <p class="price" style="<?php echo $is_really_hot ? 'color:#e74c3c;' : ''; ?>">฿ <?php echo number_format($row['price'], 2); ?></p>
                
                <?php if($is_really_hot): ?>
                    <div class="sold-count">ขายแล้ว <?php echo number_format($row['total_sold']); ?> ชิ้น</div>
                <?php endif; ?>

                <?php if(!is_admin()): ?>
                    <form class="inline" action="cart.php" method="post" style="margin-top: 15px;">
                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                        <input type="hidden" name="qty" value="1">
                        <button name="action" value="add" style="width: 100%;">หยิบลงตะกร้า</button>
                    </form>
                <?php else: ?>
                    <a href="edit_product.php?id=<?php echo $row['id']; ?>" style="display: block; text-align: center; background: #f39c12; color: white; text-decoration: none; padding: 10px; border-radius: 8px; margin-top: 15px; font-weight: bold;">✏️ แก้ไข</a>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    </div>

    <div class="section-title">
        <h2>✨ สินค้ามาใหม่ (New Arrivals)</h2>
    </div>

    <div class="grid" style="margin-bottom: 50px;">
        <?php
        // ดึงสินค้า 4 รายการล่าสุดที่เพิ่งลงในระบบ
        $sql_new = "SELECT id, name, price, image FROM products ORDER BY created_at DESC LIMIT 4";
        $res_new = $conn->query($sql_new);

        while($row = $res_new->fetch_assoc()):
        ?>
            <div class="product" style="position: relative;">
                
                <div class="badge-new">🆕 NEW</div>

                <a href="product_detail.php?id=<?php echo $row['id']; ?>" style="text-decoration: none; color: inherit;">
                    <img src="<?php echo htmlspecialchars($row['image'] ?: 'placeholder.png'); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                    <h3 style="transition: color 0.2s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='inherit'"><?php echo htmlspecialchars($row['name']); ?></h3>
                </a>
                
                <p class="price">฿ <?php echo number_format($row['price'], 2); ?></p>

                <?php if(!is_admin()): ?>
                    <form class="inline" action="cart.php" method="post" style="margin-top: 15px;">
                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                        <input type="hidden" name="qty" value="1">
                        <button name="action" value="add" style="width: 100%; background: var(--dark);">หยิบลงตะกร้า</button>
                    </form>
                <?php else: ?>
                    <a href="edit_product.php?id=<?php echo $row['id']; ?>" style="display: block; text-align: center; background: #f39c12; color: white; text-decoration: none; padding: 10px; border-radius: 8px; margin-top: 15px; font-weight: bold;">✏️ แก้ไข</a>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    </div>

</main>

<?php include_once __DIR__.'/footer.php'; ?>