<?php 
require_once __DIR__.'/db.php'; 

// รับค่าหมวดหมู่และคำค้นหาจาก URL
$category = $_GET['category'] ?? 'All';
$search_query = trim($_GET['q'] ?? '');

include_once __DIR__.'/header.php'; 
?>

<style>
    /* สไตล์สำหรับแถบปุ่มหมวดหมู่ */
    .category-tabs {
        display: flex;
        gap: 12px;
        margin-top: 25px;
        margin-bottom: 25px;
        overflow-x: auto;
        padding-bottom: 10px;
        scrollbar-width: none;
    }
    .category-tabs::-webkit-scrollbar {
        display: none;
    }
    
    .tab-btn {
        background: #fff;
        color: var(--muted);
        border: 1px solid #e6e9ee;
        padding: 10px 24px;
        border-radius: 50px;
        text-decoration: none;
        font-size: 15px;
        font-weight: 600;
        white-space: nowrap;
        transition: all 0.3s ease;
        box-shadow: 0 2px 5px rgba(0,0,0,0.02);
    }
    
    .tab-btn:hover {
        border-color: var(--primary);
        color: var(--primary);
        transform: translateY(-2px);
    }
    
    .tab-btn.active {
        background: var(--primary);
        color: #fff;
        border-color: var(--primary);
        box-shadow: 0 6px 15px rgba(31, 111, 235, 0.25);
    }
</style>

<main>
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
        <h2 style="margin: 0; color: var(--dark); font-size: 24px;">🛍️ รายการสินค้า</h2>
        
        <?php if(!empty($search_query)): ?>
            <div style="background: #eef2ff; color: var(--primary); padding: 8px 16px; border-radius: 50px; font-weight: bold; font-size: 14px;">
                🔎 ผลการค้นหาสำหรับ: "<?php echo htmlspecialchars($search_query); ?>"
                <a href="products.php?category=<?php echo urlencode($category); ?>" style="color: #e74c3c; text-decoration: none; margin-left: 10px;">✖ ล้างการค้นหา</a>
            </div>
        <?php endif; ?>
    </div>

    <?php $q_param = !empty($search_query) ? '&q=' . urlencode($search_query) : ''; ?>
    <div class="category-tabs">
        <a href="products.php?category=All<?= $q_param ?>" class="tab-btn <?= $category === 'All' ? 'active' : '' ?>">รวมทั้งหมด</a>
        <a href="products.php?category=Sneakers<?= $q_param ?>" class="tab-btn <?= $category === 'Sneakers' ? 'active' : '' ?>">👟 รองเท้าสตรีทแวร์</a>
        <a href="products.php?category=Running<?= $q_param ?>" class="tab-btn <?= $category === 'Running' ? 'active' : '' ?>">🏃‍♂️ รองเท้าวิ่ง</a>
        <a href="products.php?category=Sandals<?= $q_param ?>" class="tab-btn <?= $category === 'Sandals' ? 'active' : '' ?>">🩴 รองเท้าแตะ</a>
    </div>

    <div style="max-width: 600px; margin: 0 auto 40px auto; text-align: center;">
        <h2 style="margin-bottom: 20px; color: var(--dark);">ค้นหารองเท้าคู่โปรดของคุณ 👟</h2>
        
        <form action="products.php" method="GET" style="display: flex; gap: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border-radius: 50px; background: #fff; padding: 5px;">
            <input type="hidden" name="category" value="<?php echo htmlspecialchars($category); ?>">
            
            <input type="text" name="q" placeholder="พิมพ์ชื่อสินค้าที่ต้องการค้นหา..." value="<?php echo htmlspecialchars($search_query); ?>" style="flex: 1; padding: 15px 20px; border: none; border-radius: 50px; outline: none; font-size: 16px; background: transparent;">
            <button type="submit" style="padding: 12px 30px; background: var(--primary, #1f6feb); color: white; border: none; border-radius: 50px; cursor: pointer; font-weight: bold; font-size: 16px; transition: background 0.2s;">🔍 ค้นหา</button>
        </form>
    </div>

    <div class="grid" style="margin-bottom: 50px;">
        <?php
        // 🌟 สร้างคำสั่ง SQL แบบไดนามิก (ฉลาดขึ้น!)
        $sql = "SELECT id, name, description, price, image FROM products WHERE 1=1 ";
        $params = [];
        $types = "";

        // ถ้ามีการเลือกหมวดหมู่ที่ไม่ใช่ All
        if ($category !== 'All') {
            $sql .= " AND category = ? ";
            $params[] = $category;
            $types .= "s";
        }

        // ถ้ามีการพิมพ์ค้นหา (ค้นหาทั้งในชื่อ และ คำอธิบาย)
        if (!empty($search_query)) {
            $sql .= " AND (name LIKE ? OR description LIKE ?) ";
            $like_term = "%{$search_query}%";
            $params[] = $like_term;
            $params[] = $like_term;
            $types .= "ss";
        }

        $sql .= " ORDER BY created_at DESC";

        $stmt = $conn->prepare($sql);
        
        // ถ้ามีเงื่อนไข (หมวดหมู่ หรือ ค้นหา) ให้ทำการ Binding Parameters
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $res = $stmt->get_result();
        
        if ($res->num_rows > 0):
            while($row = $res->fetch_assoc()):
        ?>
            <div class="product">
                <a href="product_detail.php?id=<?php echo $row['id']; ?>" style="text-decoration: none; color: inherit;">
                    <img src="<?php echo htmlspecialchars($row['image'] ?: 'placeholder.png'); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                    <h3 style="transition: color 0.2s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='inherit'"><?php echo htmlspecialchars($row['name']); ?></h3>
                </a>
                
                <p><?php echo htmlspecialchars($row['description']); ?></p>
                <p class="price">ราคา <?php echo number_format($row['price'], 2); ?> ฿</p>
                <form class="inline" action="cart.php" method="post">
                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                    <input type="number" name="qty" value="1" min="1">
                    <button name="action" value="add">เพิ่มลงตะกร้า</button>
                </form>
            </div>
        <?php 
            endwhile;
        else:
            // ข้อความเมื่อค้นหาไม่เจอ
            echo "<div style='grid-column: 1 / -1; text-align: center; padding: 60px 20px; background: #fff; border-radius: 12px; color: var(--muted); border: 2px dashed #eee;'>
                    <div style='font-size: 40px; margin-bottom: 10px;'>🕵️‍♂️</div>
                    <h3 style='margin: 0 0 10px; color: var(--dark);'>ไม่พบสินค้าที่คุณค้นหา</h3>
                    <p style='margin: 0;'>ลองใช้คำค้นหาอื่น หรือเลือกดูหมวดหมู่ทั้งหมดดูสิครับ</p>
                    <a href='products.php' class='btn' style='display: inline-block; margin-top: 20px; padding: 10px 20px;'>ดูสินค้าทั้งหมด</a>
                  </div>";
        endif;
        ?>
    </div>
</main>

<?php include_once __DIR__.'/footer.php'; ?>