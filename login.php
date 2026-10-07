<?php 
require_once __DIR__.'/db.php'; // ดึงไฟล์เชื่อมฐานข้อมูล
// เช็คว่าถ้าล็อกอินแล้วให้เด้งไปหน้าแรกเลย
if(isset($_SESSION['user'])){ header('Location: index.php'); exit; }

$error = '';
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if($username && $password){
        // โค้ดตรวจสอบการล็อกอิน (ปรับให้ตรงกับฐานข้อมูลของคุณ)
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($user = $result->fetch_assoc()){
            if(password_verify($password, $user['password'])){
                $_SESSION['user'] = $user;
                header('Location: index.php');
                exit;
            } else {
                $error = 'รหัสผ่านไม่ถูกต้อง';
            }
        } else {
            $error = 'ไม่พบชื่อผู้ใช้หรืออีเมลนี้';
        }
    } else {
        $error = 'กรุณากรอกข้อมูลให้ครบถ้วน';
    }
}

require_once __DIR__.'/header.php'; 
?>

<style>
    /* ตั้งค่าหน้าพื้นหลังให้ดูเต็มจอและจัดกลาง */
    .login-wrapper {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 70vh; /* ให้กล่องอยู่กลางๆ จอพอดี */
        padding: 20px;
    }

    /* กล่องเข้าสู่ระบบ */
    .login-card {
        background: #ffffff;
        width: 100%;
        max-width: 420px;
        padding: 40px;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08); /* เงาฟุ้งๆ ดูมีราคา */
    }

    .login-card h2 {
        text-align: center;
        margin-top: 0;
        margin-bottom: 10px;
        color: #111;
        font-size: 28px;
    }

    .login-subtitle {
        text-align: center;
        color: #666;
        margin-bottom: 30px;
        font-size: 15px;
    }

    /* สไตล์ช่องกรอกข้อมูล */
    .input-group {
        margin-bottom: 20px;
    }

    .input-group label {
        display: block;
        font-weight: 600;
        margin-bottom: 8px;
        color: #444;
        font-size: 14px;
    }

    .input-group input {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid #dcdfe6;
        border-radius: 8px;
        font-size: 15px;
        transition: all 0.3s;
        background: #fcfcfc;
    }

    /* เอฟเฟกต์ตอนคลิกพิมพ์ */
    .input-group input:focus {
        border-color: #1f6feb;
        background: #fff;
        box-shadow: 0 0 0 4px rgba(31, 111, 235, 0.1); /* ขอบเรืองแสงสีน้ำเงิน */
        outline: none;
    }

    /* ปุ่มกดหลัก */
    .btn-primary {
        width: 100%;
        background: linear-gradient(90deg, #1f6feb, #315ef0);
        color: white;
        padding: 14px;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        transition: transform 0.2s, box-shadow 0.2s;
        margin-top: 10px;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(31, 111, 235, 0.3);
    }
    
    .btn-primary:active {
        transform: translateY(0);
    }

    /* ส่วนลิงก์ด้านล่าง */
    .login-footer {
        margin-top: 25px;
        text-align: center;
        font-size: 14px;
        color: #666;
    }

    .login-footer a {
        color: #1f6feb;
        text-decoration: none;
        font-weight: 600;
        transition: color 0.2s;
    }

    .login-footer a:hover {
        color: #1557c0;
        text-decoration: underline;
    }

    .forgot-password {
        display: block;
        text-align: right;
        font-size: 13px;
        color: #1f6feb;
        text-decoration: none;
        margin-top: -10px;
        margin-bottom: 20px;
    }
    
    .forgot-password:hover { text-decoration: underline; }

    /* กล่องแสดง Error */
    .alert-error {
        background: #fdeaea;
        color: #e74c3c;
        padding: 12px;
        border-radius: 8px;
        font-size: 14px;
        margin-bottom: 20px;
        text-align: center;
        border: 1px solid #f5c6c6;
    }
</style>

<div class="login-wrapper">
    <div class="login-card">
        <h2>เข้าสู่ระบบ</h2>
        <p class="login-subtitle">ยินดีต้อนรับกลับมา! กรุณาล็อกอินเพื่อดำเนินการต่อ</p>
        
        <?php if(!empty($error)): ?>
            <div class="alert-error">
                ⚠️ <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="input-group">
                <label>ชื่อผู้ใช้ หรือ อีเมล</label>
                <input type="text" name="username" placeholder="กรอกชื่อผู้ใช้หรืออีเมลของคุณ" required>
            </div>
            
            <div class="input-group">
                <label>รหัสผ่าน</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>
            
            <a href="forgot_password.php" class="forgot-password">ลืมรหัสผ่าน?</a>
            
            <button type="submit" class="btn-primary">ล็อกอินเข้าสู่ระบบ</button>
        </form>

        <div class="login-footer">
            ยังไม่มีบัญชีใช่ไหม? <a href="register.php">สมัครสมาชิกเลย</a>
            <br><br>
            <a href="index.php" style="color: #888; font-weight: normal; font-size: 13px;">← กลับไปหน้าแรก</a>
        </div>
    </div>
</div>

<?php require_once __DIR__.'/footer.php'; ?>