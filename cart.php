<?php
// เริ่มระบบ Session เพื่อเก็บข้อมูลตะกร้าสินค้า
if(session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__.'/db.php';

// สร้างตะกร้าว่างๆ ถ้ายังไม่มี
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// ================= จัดการระบบตะกร้า (เพิ่ม / แก้ไข / ลบ) =================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = intval($_POST['id'] ?? 0);
    $qty = intval($_POST['qty'] ?? 1);

    if ($id > 0) {
        if ($action === 'add') {
            // ถ้ามีสินค้าอยู่แล้วให้บวกจำนวนเพิ่ม ถ้ายังไม่มีให้ตั้งค่าเริ่มต้น
            if (isset($_SESSION['cart'][$id])) {
                $_SESSION['cart'][$id] += $qty;
            } else {
                $_SESSION['cart'][$id] = $qty;
            }
        } elseif ($action === 'update') {
            // อัปเดตจำนวนสินค้า
            if ($qty > 0) {
                $_SESSION['cart'][$id] = $qty;
            } else {
                unset($_SESSION['cart'][$id]); // ถ้าจำนวนเป็น 0 ให้ลบออก
            }
        } elseif ($action === 'remove') {
            // ลบสินค้าออกจากตะกร้า
            unset($_SESSION['cart'][$id]);
        }
        
        // รีไดเรกต์กลับมาหน้าเดิมเพื่อป้องกันปัญหาการกด F5 แล้วฟอร์มส่งซ้ำ
        header('Location: cart.php');
        exit;
    }
}

include_once __DIR__.'/header.php'; 
?>

<style>
    .cart-wrapper {
        display: flex;
        flex-wrap: wrap;
        gap: 30px;
        margin-top: 20px;
        margin-bottom: 50px;
    }
    
    .cart-items {
        flex: 2;
        min-width: 300px;
    }
    
    .cart-summary {
        flex: 1;
        min-width: 300px;
        background: var(--card);
        padding: 30px;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        height: fit-content;
        position: sticky;
        top: 100px; /* ลอยติดขอบจอบน */
    }

    .cart-item-card {
        display: flex;
        align-items: center;
        gap: 20px;
        background: var(--card);
        padding: 20px;
        border-radius: 16px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        margin-bottom: 15px;
        position: relative;
    }

    .cart-item-img {
        width: 100px;
        height: 100px;
        object-fit: contain;
        background: #f8f9fa;
        border-radius: 10px;
        padding: 5px;
        border: 1px solid #eee;
    }

    .cart-item-info {
        flex: 1;
    }

    .cart-item-title {
        margin: 0 0 5px 0;
        font-size: 18px;
        color: var(--dark);
    }

    .cart-item-price {
        color: var(--primary);
        font-weight: bold;
        font-size: 16px;
    }

    .cart-item-actions {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .qty-input {
        width: 60px;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 8px;
        text-align: center;
        font-size: 16px;
    }

    .btn-update {
        background: #f0f0f0;
        color: #333;
        border: none;
        padding: 8px 12px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: bold;
    }
    .btn-update:hover { background: #e0e0e0; }

    .btn-remove {
        background: #ffeaea;
        color: #e74c3c;
        border: none;
        padding: 8px 12px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: bold;
        transition: all 0.2s;
    }
    .btn-remove:hover {
        background: #e74c3c;
        color: #fff;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 15px;
        font-size: 16px;
        color: #555;
    }

    .summary-total {
        display: flex;
        justify-content: space-between;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 2px dashed #eee;
        font-size: 22px;
        font-weight: bold;
        color: var(--dark);
    }

    .btn-checkout {
        display: block;
        width: 100%;
        background: #27ae60;
        color: #fff;
        text-align: center;
        padding: 15px;
        border-radius: 10px;
        font-size: 18px;
        font-weight: bold;
        text-decoration: none;
        margin-top: 25px;
        border: none;
        cursor: pointer;
        transition: transform 0.2s;
    }
    .btn-checkout:hover {
        background: #219653;
        transform: translateY(-2px);
    }

    .empty-cart {
        text-align: center;
        padding: 60px 20px;
        background: var(--card);
        border-radius: 16px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
    }
</style>

<main style="max-width: 1100px; margin: 0 auto; padding: 20px;">
    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px;">
        <h2 style="margin: 0; font-size: 28px; color: var(--dark);">🛒 ตะกร้าสินค้าของคุณ</h2>
    </div>

    <?php if (empty($_SESSION['cart'])): ?>
        <div class="empty-cart">
            <div style="font-size: 60px; margin-bottom: 20px;">🛍️</div>
            <h3 style="margin-top: 0; color: var(--dark);">ตะกร้าสินค้าว่างเปล่า</h3>
            <p style="color: var(--muted); margin-bottom: 30px;">คุณยังไม่ได้เลือกสินค้าใดๆ ลงตะกร้า ลองดูสินค้ามาใหม่ของเราสิครับ!</p>
            <a href="index.php" class="btn" style="padding: 12px 30px; font-size: 16px;">ไปช้อปปิ้งกันเลย</a>
        </div>
    <?php else: ?>
        <div class="cart-wrapper">
            <div class="cart-items">
                <?php
                $total_price = 0;
                $total_items = 0;
                
                // ดึงข้อมูลสินค้าที่อยู่ในตะกร้าจากฐานข้อมูล
                $cart_ids = array_keys($_SESSION['cart']);
                $placeholders = implode(',', array_fill(0, count($cart_ids), '?'));
                $stmt = $conn->prepare("SELECT id, name, price, image FROM products WHERE id IN ($placeholders)");
                
                // Binding parameters แบบไดนามิก (เพราะไม่รู้ว่ามีสินค้ากี่ชิ้น)
                $types = str_repeat('i', count($cart_ids));
                $stmt->bind_param($types, ...$cart_ids);
                $stmt->execute();
                $result = $stmt->get_result();

                while ($row = $result->fetch_assoc()):
                    $qty = $_SESSION['cart'][$row['id']];
                    $subtotal = $row['price'] * $qty;
                    $total_price += $subtotal;
                    $total_items += $qty;
                ?>
                    <div class="cart-item-card">
                        <img src="<?php echo htmlspecialchars($row['image'] ?: 'placeholder.png'); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" class="cart-item-img">
                        
                        <div class="cart-item-info">
                            <h4 class="cart-item-title"><?php echo htmlspecialchars($row['name']); ?></h4>
                            <div class="cart-item-price">฿ <?php echo number_format($row['price'], 2); ?> / ชิ้น</div>
                        </div>

                        <div class="cart-item-actions">
                            <form method="post" style="display: flex; align-items: center; gap: 8px; margin: 0;">
                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                <input type="hidden" name="action" value="update">
                                <input type="number" name="qty" value="<?php echo $qty; ?>" min="1" class="qty-input">
                                <button type="submit" class="btn-update">🔄 อัปเดต</button>
                            </form>

                            <form method="post" style="margin: 0;" onsubmit="return confirm('ต้องการลบสินค้านี้ออกจากตะกร้าใช่ไหม?');">
                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                <input type="hidden" name="action" value="remove">
                                <button type="submit" class="btn-remove">🗑️ ลบ</button>
                            </form>
                        </div>
                        
                        <div style="font-weight: bold; font-size: 18px; width: 100px; text-align: right; color: var(--dark);">
                            ฿ <?php echo number_format($subtotal, 2); ?>
                        </div>
                    </div>
                <?php endwhile; ?>
                
                <a href="index.php" style="display: inline-block; margin-top: 10px; color: var(--primary); text-decoration: none; font-weight: bold;">← เลือกซื้อสินค้าต่อ</a>
            </div>

            <div class="cart-summary">
                <h3 style="margin-top: 0; border-bottom: 2px solid #eee; padding-bottom: 15px; color: var(--dark);">🧾 สรุปคำสั่งซื้อ</h3>
                
                <div class="summary-row">
                    <span>จำนวนสินค้าทั้งหมด</span>
                    <span><?php echo $total_items; ?> ชิ้น</span>
                </div>
                <div class="summary-row">
                    <span>ยอดรวมสินค้า</span>
                    <span>฿ <?php echo number_format($total_price, 2); ?></span>
                </div>
                <div class="summary-row">
                    <span>ค่าจัดส่ง</span>
                    <span style="color: #27ae60; font-weight: bold;">จัดส่งฟรี!</span>
                </div>

                <div class="summary-total">
                    <span>ยอดสุทธิ</span>
                    <span style="color: #e74c3c;">฿ <?php echo number_format($total_price, 2); ?></span>
                </div>

                <a href="checkout.php" class="btn-checkout">💳 ดำเนินการชำระเงิน</a>
            </div>
        </div>
    <?php endif; ?>
</main>

<?php include_once __DIR__.'/footer.php'; ?>