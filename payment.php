<?php
if(session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__.'/db.php';

if (!isset($_SESSION['user']) || !isset($_GET['order_id'])) {
    header('Location: index.php');
    exit;
}

$order_id = intval($_GET['order_id']);
$user_id = $_SESSION['user']['id'];

// ดึงยอดเงินของบิลนี้มาแสดง
$stmt = $conn->prepare("SELECT total_amount, status, created_at FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order || $order['status'] !== 'pending') {
    echo "<script>alert('ออเดอร์นี้ชำระเงินไปแล้ว หรือไม่พบข้อมูล'); window.location='my_orders.php';</script>";
    exit;
}

// ================= ส่วนจัดการเมื่อจำลองการจ่ายเงินสำเร็จ =================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pay_now'])) {
    // อัปเดตสถานะในฐานข้อมูลเป็น 'paid' (ชำระเงินแล้ว)
    $update_stmt = $conn->prepare("UPDATE orders SET status = 'paid' WHERE id = ?");
    $update_stmt->bind_param("i", $order_id);
    if($update_stmt->execute()){
        echo "<script>
            alert('🎉 ชำระเงินสำเร็จ! ระบบได้ยืนยันการสั่งซื้อของคุณแล้ว');
            window.location='my_orders.php';
        </script>";
        exit;
    }
}

// สร้างเลขบิลสมจริงมาโชว์
$date_str = date('Ymd', strtotime($order['created_at']));
$realistic_order_id = sprintf("ORD-%s-%04d", $date_str, $order_id);

include_once __DIR__.'/header.php';
?>

<style>
    .payment-container {
        max-width: 500px;
        margin: 40px auto;
        background: var(--card, #fff);
        padding: 40px;
        border-radius: 20px;
        box-shadow: 0 15px 40px rgba(0,0,0,0.08);
        text-align: center;
    }

    .qr-box {
        background: #f8f9fa;
        border: 2px dashed #ccc;
        padding: 20px;
        border-radius: 16px;
        margin: 20px 0;
        display: inline-block;
    }

    .amount-display {
        font-size: 36px;
        font-weight: bold;
        color: var(--primary, #1f6feb);
        margin: 15px 0;
    }

    .btn-pay {
        background: #27ae60;
        color: white;
        border: none;
        width: 100%;
        padding: 16px;
        border-radius: 12px;
        font-size: 18px;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.2s;
        margin-top: 15px;
    }
    .btn-pay:hover { background: #219653; transform: translateY(-2px); }

    /* CSS สำหรับหน้าจอโหลดดิ้ง (ซ่อนไว้ตอนแรก) */
    #loading-screen {
        display: none;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        margin-top: 20px;
    }
    
    .spinner {
        width: 50px;
        height: 50px;
        border: 5px solid #f3f3f3;
        border-top: 5px solid var(--primary);
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin-bottom: 15px;
    }
    
    @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
</style>

<main style="padding: 20px;">
    <div class="payment-container" id="payment-ui">
        <h2 style="margin-top:0; color:var(--dark);">ชำระเงินผ่าน PromptPay</h2>
        <p style="color:var(--muted); margin-bottom: 5px;">หมายเลขคำสั่งซื้อ: <strong><?php echo $realistic_order_id; ?></strong></p>
        
        <div class="amount-display">
            ฿ <?php echo number_format($order['total_amount'], 2); ?>
        </div>

        <div class="qr-box">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=MockupPayment_<?php echo $order_id; ?>" alt="QR Code" style="border-radius: 8px;">
            <p style="margin: 10px 0 0 0; font-size: 14px; color: #555;">สแกนเพื่อชำระเงิน</p>
        </div>

        <form method="post" id="payment-form">
            <input type="hidden" name="pay_now" value="1">
            <button type="button" class="btn-pay" onclick="simulatePayment()">📲 จำลองการโอนเงินสำเร็จ</button>
        </form>
        
        <a href="my_orders.php" style="display:block; margin-top: 20px; color: var(--muted); text-decoration: none;">ชำระเงินภายหลัง</a>
    </div>

    <div class="payment-container" id="loading-screen">
        <div class="spinner"></div>
        <h3 style="color: var(--dark);">กำลังเชื่อมต่อกับธนาคาร...</h3>
        <p style="color: var(--primary); font-weight: bold;">โปรดอย่าปิดหน้าต่างนี้ ระบบกำลังตรวจสอบยอดเงินของคุณโดยอัตโนมัติ</p>
    </div>
</main>

<script>
    function simulatePayment() {
        // 1. ซ่อนหน้าจอจ่ายเงิน แล้วโชว์หน้าจอหมุนติ้วๆ
        document.getElementById('payment-ui').style.display = 'none';
        document.getElementById('loading-screen').style.display = 'flex';

        // 2. หน่วงเวลา 3 วินาที (3000 มิลลิวินาที) เพื่อความสมจริง แล้วค่อยส่งฟอร์มบันทึกฐานข้อมูล
        setTimeout(function() {
            document.getElementById('payment-form').submit();
        }, 3000);
    }
</script>

<?php include_once __DIR__.'/footer.php'; ?>