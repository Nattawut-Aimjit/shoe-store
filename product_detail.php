<?php 
if(session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__.'/db.php'; 

// ตรวจสอบว่ามี ID สินค้าส่งมาหรือไม่
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: products.php');
    exit;
}

$id = intval($_GET['id']);

// ดึงข้อมูลสินค้าจากฐานข้อมูล
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

// ถ้าไม่เจอสินค้านี้ ให้เด้งกลับไปหน้าสินค้าทั้งหมด
if (!$product) {
    echo "<script>alert('ไม่พบสินค้านี้ หรือสินค้าอาจถูกลบไปแล้ว'); window.location='products.php';</script>";
    exit;
}

include_once __DIR__.'/header.php'; 
?>

<style>
    /* แถบบอกทาง (Breadcrumbs) */
    .breadcrumb {
        margin: 20px 0 30px 0;
        font-size: 14px;
        color: var(--muted);
    }
    .breadcrumb a {
        color: var(--primary);
        text-decoration: none;
        font-weight: 500;
    }
    .breadcrumb a:hover { text-decoration: underline; }

    /* เค้าโครงหลัก (Layout) */
    .product-detail-container {
        display: flex;
        gap: 50px;
        background: var(--card, #fff);
        padding: 40px;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.03);
        margin-bottom: 50px;
        align-items: flex-start;
    }

    /* ฝั่งซ้าย: รูปภาพ */
    .product-gallery {
        flex: 1;
        min-width: 300px;
        background: #f8f9fa;
        border-radius: 16px;
        padding: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #eee;
    }
    .product-main-img {
        width: 100%;
        max-width: 450px;
        height: auto;
        object-fit: contain;
        transition: transform 0.3s ease;
    }
    .product-main-img:hover {
        transform: scale(1.05); /* เอฟเฟกต์ซูมเมื่อเอาเมาส์ชี้ */
    }

    /* ฝั่งขวา: ข้อมูลสินค้า */
    .product-info {
        flex: 1;
        min-width: 300px;
    }
    
    .product-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 50px;
        font-size: 12px;
        font-weight: bold;
        margin-bottom: 15px;
        margin-right: 10px;
    }
    .badge-category { background: var(--dark); color: white; }
    .badge-hot { background: #fff5f5; color: #e74c3c; border: 1px solid #f5c6cb; }

    .product-title {
        font-size: 32px;
        font-weight: 800;
        color: var(--dark);
        margin: 0 0 15px 0;
        line-height: 1.2;
    }

    .product-price {
        font-size: 36px;
        font-weight: bold;
        color: var(--primary);
        margin-bottom: 25px;
    }

    .product-description {
        font-size: 16px;
        line-height: 1.8;
        color: #555;
        margin-bottom: 30px;
        padding-top: 25px;
        border-top: 1px solid #eee;
    }

    /* ฟอร์มหยิบลงตะกร้า */
    .add-to-cart-form {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-top: 30px;
        padding-top: 30px;
        border-top: 1px solid #eee;
    }
    
    .qty-input {
        width: 80px;
        padding: 15px;
        border: 2px solid #ddd;
        border-radius: 12px;
        font-size: 18px;
        font-weight: bold;
        text-align: center;
        outline: none;
        transition: border-color 0.2s;
    }
    .qty-input:focus { border-color: var(--primary); }

    .btn-add-cart {
        flex: 1;
        background: var(--primary);
        color: white;
        border: none;
        padding: 18px 30px;
        font-size: 18px;
        font-weight: bold;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
        box-shadow: 0 4px 15px rgba(31, 111, 235, 0.3);
    }
    .btn-add-cart:hover {
        background: #1557c0;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(31, 111, 235, 0.4);
    }

    /* รองรับหน้าจอมือถือ */
    @media (max-width: 800px) {
        .product-detail-container { flex-direction: column; gap: 30px; padding: 25px; }
        .product-gallery { width: 100%; box-sizing: border-box; }
        .add-to-cart-form { flex-direction: column; align-items: stretch; }
        .qty-input { width: 100%; box-sizing: border-box; }
    }
</style>

<main style="max-width: 1200px; margin: 0 auto; padding: 20px;">
    
    <div class="breadcrumb">
        <a href="index.php">หน้าหลัก</a> &nbsp; ❯ &nbsp; 
        <a href="products.php">สินค้าทั้งหมด</a> &nbsp; ❯ &nbsp; 
        <a href="products.php?category=<?php echo urlencode($product['category'] ?? 'All'); ?>">
            <?php 
                $cat = $product['category'] ?? 'ไม่ระบุหมวดหมู่';
                if($cat === 'Sneakers') echo 'รองเท้าสตรีทแวร์';
                elseif($cat === 'Running') echo 'รองเท้าวิ่ง';
                elseif($cat === 'Sandals') echo 'รองเท้าแตะ';
                else echo $cat;
            ?>
        </a> &nbsp; ❯ &nbsp; 
        <span style="color: var(--dark); font-weight: bold;"><?php echo htmlspecialchars($product['name']); ?></span>
    </div>

    <div class="product-detail-container">
        
        <div class="product-gallery">
            <img src="<?php echo htmlspecialchars($product['image'] ?: 'placeholder.png'); ?>" 
                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                 class="product-main-img">
        </div>

        <div class="product-info">
            
            <div>
                <span class="product-badge badge-category">
                    <?php echo htmlspecialchars($product['category'] ?? 'Sneakers'); ?>
                </span>
                <?php if(!empty($product['is_popular']) && $product['is_popular'] == 1): ?>
                    <span class="product-badge badge-hot">🔥 สินค้ายอดฮิต</span>
                <?php endif; ?>
            </div>

            <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
            
            <div class="product-price">
                ฿ <?php echo number_format($product['price'], 2); ?>
            </div>

            <div class="product-description">
                <strong style="color: var(--dark); display: block; margin-bottom: 10px;">รายละเอียดสินค้า:</strong>
                <?php echo nl2br(htmlspecialchars($product['description'])); ?>
            </div>

            <?php if(!is_admin()): // ซ่อนปุ่มหยิบลงตะกร้าถ้าเป็นแอดมิน ?>
                <form class="add-to-cart-form" action="cart.php" method="post">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                    
                    <div>
                        <label style="display: block; font-size: 13px; color: var(--muted); margin-bottom: 5px; font-weight: bold;">จำนวน</label>
                        <input type="number" name="qty" value="1" min="1" class="qty-input">
                    </div>
                    
                    <button type="submit" class="btn-add-cart" style="margin-top: 22px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="9" cy="21" r="1"></circle>
                            <circle cx="20" cy="21" r="1"></circle>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                        </svg>
                        หยิบใส่ตะกร้า
                    </button>
                </form>
            <?php else: ?>
                <div style="margin-top: 30px; padding: 20px; background: #fff3cd; border: 1px dashed #ffeeba; border-radius: 12px; color: #856404; text-align: center; font-weight: bold;">
                    ⚠️ แอดมินไม่สามารถทำการสั่งซื้อสินค้าได้
                    <br>
                    <a href="edit_product.php?id=<?php echo $product['id']; ?>" style="display: inline-block; margin-top: 10px; color: #e67e22; text-decoration: underline;">✏️ กดที่นี่เพื่อแก้ไขสินค้านี้</a>
                </div>
            <?php endif; ?>
            
        </div>

    </div>

</main>

<?php include_once __DIR__.'/footer.php'; ?>