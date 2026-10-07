<?php include_once __DIR__.'/db.php';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $username = trim($_POST['username']);
  $email = trim($_POST['email']);
  $password = $_POST['password'];
  if($username && $email && $password){
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare('INSERT INTO users (username,email,password) VALUES (?,?,?)');
    $stmt->bind_param('sss',$username,$email,$hash);
    if($stmt->execute()){
      header('Location: login.php'); exit;
    } else {
      $error = 'ไม่สามารถสมัครได้: '.$conn->error;
    }
  } else $error = 'กรุณากรอกข้อมูลให้ครบ';
}
include_once __DIR__.'/header.php';
?>
<div class="auth-wrap">
  <h2>สมัครสมาชิก</h2>
  <?php if(!empty($error)) echo '<p style="color:red">'.htmlspecialchars($error).'</p>'; ?>
  <form method="post">
    <div class="form-field">
      <label>ชื่อผู้ใช้</label>
      <input name="username" placeholder="ชื่อผู้ใช้" required>
    </div>
    <div class="form-field">
      <label>อีเมล</label>
      <input name="email" type="email" placeholder="you@example.com" required>
    </div>
    <div class="form-field">
      <label>รหัสผ่าน</label>
      <input name="password" type="password" placeholder="รหัสผ่าน" required>
    </div>
    <div class="form-actions">
      <button class="btn" type="submit">สมัคร</button>
      <a class="btn secondary" href="/shoestore/login.php">ไปที่ล็อกอิน</a>
    </div>
    <div class="help-links">
      <a href="/shoestore/">กลับหน้าแรก</a>
      <span></span>
    </div>
  </form>
</div>
<?php include_once __DIR__.'/footer.php'; ?>
