<?php
include_once __DIR__.'/db.php';

$dataFile = __DIR__ . '/people_data.json';
if(!file_exists($dataFile)) file_put_contents($dataFile, json_encode([], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
$people = json_decode(file_get_contents($dataFile), true) ?? [];

// Handle save from admin
if(is_admin() && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save'){
    $id = intval($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $link = trim($_POST['link'] ?? '#');

    // find index
    $idx = null;
    foreach($people as $k=>$p) if($p['id']==$id) { $idx=$k; break; }
    if($idx===null){ $idx = count($people); $people[$idx] = ['id'=>$id?:($idx+1),'title'=>'','description'=>'','image'=>'/shoestore/placeholder.png','link'=>'#']; }

    // handle image upload
    if(!empty($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK){
        $uploaddir = __DIR__ . '/uploads/people';
        if(!is_dir($uploaddir)) mkdir($uploaddir, 0755, true);
        $f = $_FILES['image'];
        $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif'];
        if(in_array($ext, $allowed)){
            $filename = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
            $dest = $uploaddir . '/' . $filename;
            if(move_uploaded_file($f['tmp_name'], $dest)){
                $people[$idx]['image'] = '/shoestore/uploads/people/' . $filename;
            }
        }
    }

    $people[$idx]['title'] = $title ?: $people[$idx]['title'];
    $people[$idx]['description'] = $desc ?: $people[$idx]['description'];
    $people[$idx]['link'] = $link ?: $people[$idx]['link'];

    file_put_contents($dataFile, json_encode(array_values($people), JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
    header('Location: people.php'); exit;
}

include_once __DIR__.'/header.php';
?>

<h2>เหล่าผู้สร้าง</h2>
<p class="muted">คลิกการ์ดเพื่อเข้าสู่หน้าที่เกี่ยวข้อง</p>

<div class="grid">
<?php foreach($people as $p): ?>
  <div class="product">
    <a href="<?php echo htmlspecialchars($p['link']?:'#'); ?>" style="color:inherit;text-decoration:none;display:block">
      <img src="<?php echo htmlspecialchars($p['image'] ?: 'placeholder.png'); ?>" alt="<?php echo htmlspecialchars($p['title']); ?>">
      <h3><?php echo htmlspecialchars($p['title']); ?></h3>
      <p class="muted"><?php echo htmlspecialchars($p['description']); ?></p>
    </a>
    <?php if(is_admin()): ?>
      <details style="margin-top:8px">
        <summary style="cursor:pointer">แก้ไขการ์ด</summary>
        <form method="post" enctype="multipart/form-data" style="margin-top:8px">
          <input type="hidden" name="action" value="save">
          <input type="hidden" name="id" value="<?php echo (int)$p['id']; ?>">
          <label>ชื่อ<br><input name="title" value="<?php echo htmlspecialchars($p['title']); ?>"></label><br>
          <label>คำอธิบาย<br><textarea name="description"><?php echo htmlspecialchars($p['description']); ?></textarea></label><br>
          <label>ลิงก์ถ้าต้องการให้คลิกแล้วไปไหน<br><input name="link" value="<?php echo htmlspecialchars($p['link']); ?>"></label><br>
          <label>เปลี่ยนรูป<br><input type="file" name="image" accept="image/*"></label><br>
          <button type="submit">บันทึก</button>
        </form>
      </details>
    <?php endif; ?>
  </div>
<?php endforeach; ?>
</div>

<?php include_once __DIR__.'/footer.php'; ?>
