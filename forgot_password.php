<?php 
require_once __DIR__.'/db.php';
if(session_status() === PHP_SESSION_NONE) { session_start(); }

// ถ้าล็อกอินอยู่แล้วให้เด้งไปหน้าแรก
if(isset($_SESSION['user'])){ header('Location: index.php'); exit; }

$step = 1;
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ขั้นตอนที่ 1: ตรวจสอบ Username และ Email
    if (isset($_POST['verify_identity'])) {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);

        // ค้นหาในฐานข้อมูลว่ามีบัญชีนี้หรือไม่
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $_SESSION['reset_user_id'] = $user['id']; // เก็บ ID ไว้ชั่วคราวเพื่อรีเซ็ต
            $step = 2; // เปลี่ยนไปฟอร์มตั้งรหัสผ่านใหม่
        } else {
            $error = "ชื่อผู้ใช้หรืออีเมลไม่ถูกต้อง กรุณาตรวจสอบอีกครั้ง";
        }
    } 
    // ขั้นตอนที่ 2: บันทึกรหัสผ่านใหม่
    elseif (isset($_POST['reset_password'])) {
        if (isset($_SESSION['reset_user_id'])) {
            $new_pass = $_POST['new_password'];
            $confirm_pass = $_POST['confirm_password'];

            if ($new_pass === $confirm_pass) {
                // เข้ารหัสผ่านใหม่ (ต้องใช้ password_hash เพื่อให้ตรงกับระบบ Login)
                $hashed_password = password_hash($new_pass, PASSWORD_DEFAULT);
                $userId = $_SESSION['reset_user_id'];

                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $hashed_password, $userId);

                if ($stmt->execute()) {
                    unset($_SESSION['reset_user_id']); // ล้างค่าเซสชัน
                    $step = 3; // ไปหน้าสำเร็จ
                } else {
                    $error = "เกิดข้อผิดพลาด กรุณาลองใหม่";
                    $step = 2;
                }
            } else {
                $error = "รหัสผ่านไม่ตรงกัน กรุณากรอกให้เหมือนกันทั้งสองช่อง";
                $step = 2;
            }
        } else {
            $error = "เซสชันหมดอายุ กรุณาเริ่มทำรายการใหม่";
            $step = 1;
        }
    }
}

require_once __DIR__.'/header.php'; 
?>

<main style="display: flex; align-items: center; justify-content: center; min-height: 60vh; padding: 20px;">
    <div class="auth-wrap" style="width: 100%; margin: 0;">
        
        <?php if ($step === 1): ?>
            <h2>🔒 ลืมรหัสผ่าน</h2>
            <p style="text-align: center; color: var(--muted); margin-bottom: 20px;">กรุณากรอกชื่อผู้ใช้และอีเมลที่ใช้สมัครสมาชิก เพื่อยืนยันตัวตน</p>
            
            <?php if(!empty($error)) echo "<div style='background: #fdeaea; color: #e74c3c; padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center;'>⚠️ $error</div>"; ?>

            <form method="POST" action="">
                <div class="form-field">
                    <label>ชื่อผู้ใช้ (Username)</label>
                    <input type="text" name="username" required placeholder="กรอกชื่อผู้ใช้ของคุณ">
                </div>
                <div class="form-field">
                    <label>อีเมล (Email)</label>
                    <input type="email" name="email" required placeholder="example@email.com">
                </div>
                <button type="submit" name="verify_identity" class="btn" style="width: 100%; margin-top: 10px;">ตรวจสอบข้อมูล</button>
            </form>
            <div class="center-small">
                <a href="login.php" style="color: var(--primary); text-decoration: none;">← กลับไปหน้าเข้าสู่ระบบ</a>
            </div>

        <?php elseif ($step === 2): ?>
            <h2>🔑 ตั้งรหัสผ่านใหม่</h2>
            <p style="text-align: center; color: #27ae60; margin-bottom: 20px;">ยืนยันตัวตนสำเร็จ! กรุณาตั้งรหัสผ่านใหม่ของคุณ</p>

            <?php if(!empty($error)) echo "<div style='background: #fdeaea; color: #e74c3c; padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center;'>⚠️ $error</div>"; ?>

            <form method="POST" action="">
                <div class="form-field">
                    <label>รหัสผ่านใหม่</label>
                    <input type="password" name="new_password" required placeholder="รหัสผ่านใหม่อย่างน้อย 6 ตัวอักษร" minlength="6">
                </div>
                <div class="form-field">
                    <label>ยืนยันรหัสผ่านใหม่</label>
                    <input type="password" name="confirm_password" required placeholder="กรอกรหัสผ่านใหม่อีกครั้ง">
                </div>
                <button type="submit" name="reset_password" class="btn" style="width: 100%; margin-top: 10px; background: #27ae60;">บันทึกรหัสผ่านใหม่</button>
            </form>

        <?php elseif ($step === 3): ?>
            <div style="text-align: center; padding: 20px 0;">
                <div style="font-size: 50px; margin-bottom: 10px;">✅</div>
                <h2 style="color: #27ae60; margin-bottom: 10px;">เปลี่ยนรหัสผ่านสำเร็จ!</h2>
                <p style="color: var(--muted); margin-bottom: 25px;">คุณสามารถใช้รหัสผ่านใหม่เพื่อเข้าสู่ระบบได้ทันที</p>
                <a href="login.php" class="btn" style="width: 100%;">ไปหน้าเข้าสู่ระบบ</a>
            </div>
        <?php endif; ?>

    </div>
</main>

<?php require_once __DIR__.'/footer.php'; ?>