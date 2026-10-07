<?php include_once __DIR__.'/db.php';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $email = trim($_POST['email'] ?? '');
  if($email){
    // Basic lookup
    $stmt = $conn->prepare('SELECT id,username,email FROM users WHERE email=? LIMIT 1');
    $stmt->bind_param('s',$email);
    $stmt->execute(); $res = $stmt->get_result();
    if($user = $res->fetch_assoc()){
      // In a real app: generate token, email it. Here show a friendly message.
      $msg = 'ส่งคำแนะนำการตั้งรหัสผ่านใหม่ไปยังอีเมล (จำลอง)';
    } else {
      $error = 'ไม่พบบัญชีที่ใช้อีเมลนี้';
    }
  } else $error = 'กรุณากรอกอีเมล';
}
include_once __DIR__.'/header.php';
?>
<div class="auth-wrap">
  <h2>ลืมรหัสผ่าน</h2>
  <?php if(!empty($msg)) echo '<p style="color:green">'.htmlspecialchars($msg).'</p>'; ?>
  <?php if(!empty($error)) echo '<p style="color:red">'.htmlspecialchars($error).'</p>'; ?>
  <form method="post">
    <div class="form-field">
      <label>ป้อนอีเมลที่ลงทะเบียนไว้</label>
      <input name="email" type="email" placeholder="you@example.com" required>
    </div>
    <div class="form-actions">
      <button class="btn" type="submit">ส่งคำแนะนำ</button>
      <a class="btn secondary" href="login.php">กลับสู่ล็อกอิน</a>
    </div>
  </form>
</div>
<?php include_once __DIR__.'/footer.php'; ?>
