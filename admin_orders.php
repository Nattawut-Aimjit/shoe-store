<?php 
if(session_status() === PHP_SESSION_NONE) { session_start(); }
include_once __DIR__.'/db.php';

// ป้องกันคนที่ไม่ใช่แอดมิน
if(!is_admin()){ 
    header('HTTP/1.1 403 Forbidden'); 
    echo 'ต้องเป็นแอดมินเท่านั้น'; 
    exit; 
}

// ================= จัดการเมื่อแอดมินกดอัปเดตสถานะ =================
$msg = '';
$msg_type = ''; 

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status'){
    $order_id = intval($_POST['order_id']);
    $new_status = $_POST['status'];
    
    $allowed_statuses = ['pending', 'paid', 'shipped', 'cancelled'];
    if(in_array($new_status, $allowed_statuses)){
        $stmt_update = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt_update->bind_param("si", $new_status, $order_id);
        if($stmt_update->execute()){
            $msg = "อัปเดตสถานะออเดอร์ #$order_id เป็น '" . getStatusText($new_status) . "' สำเร็จแล้ว";
            $msg_type = 'success';
        } else {
            $msg = "เกิดข้อผิดพลาดในการอัปเดตสถานะ";
            $msg_type = 'error';
        }
    }
}

function getStatusText($status) {
    if($status === 'pending') return 'รอดำเนินการ';
    if($status === 'paid') return 'ชำระเงินแล้ว';
    if($status === 'shipped') return 'จัดส่งแล้ว';
    if($status === 'cancelled') return 'ยกเลิก';
    return $status;
}

// ================= ระบบค้นหาและฟิลเตอร์สถานะ + วันที่ =================
$search_query = $_GET['search'] ?? '';
$filter_status = $_GET['status'] ?? 'all';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

$sql = "SELECT o.id, o.total_amount, o.status, o.created_at, u.username 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        WHERE 1=1 ";
$params = [];
$types = "";

// 1. ค้นหาจาก ID หรือ ชื่อลูกค้า
if(!empty($search_query)) {
    $search_id = intval(str_replace(['ORD-', 'ord-'], '', $search_query));
    $sql .= " AND (o.id = ? OR u.username LIKE ?) ";
    $like_search = "%$search_query%";
    $params[] = $search_id;
    $params[] = $like_search;
    $types .= "is";
}

// 2. ฟิลเตอร์ตามสถานะ
if($filter_status !== 'all') {
    $sql .= " AND o.status = ? ";
    $params[] = $filter_status;
    $types .= "s";
}

// 🌟 3. ค้นหาตามช่วงวันที่ (Date Range)
if(!empty($start_date)) {
    $sql .= " AND o.created_at >= ? ";
    $params[] = $start_date . ' 00:00:00'; // เริ่มตั้งแต่เที่ยงคืน
    $types .= "s";
}
if(!empty($end_date)) {
    $sql .= " AND o.created_at <= ? ";
    $params[] = $end_date . ' 23:59:59'; // ถึงก่อนเที่ยงคืนของอีกวัน
    $types .= "s";
}

$sql .= " ORDER BY o.created_at DESC";

// ดึงข้อมูล
$stmt = $conn->prepare($sql);
if(!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$res = $stmt->get_result();

include_once __DIR__.'/header.php';
?>

<style>
    .admin-container {
        background: var(--card, #fff);
        padding: 30px;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.04);
        margin-bottom: 50px;
    }

    /* แถบเครื่องมือ */
    .toolbar {
        margin-bottom: 25px;
        background: #f8f9fa;
        padding: 20px;
        border-radius: 12px;
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .filter-tabs {
        display: flex;
        gap: 10px;
        overflow-x: auto;
    }
    
    .filter-btn {
        padding: 8px 16px;
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 50px;
        color: #555;
        text-decoration: none;
        font-size: 14px;
        font-weight: bold;
        transition: all 0.2s;
        white-space: nowrap;
    }
    .filter-btn:hover { background: #f0f0f0; }
    .filter-btn.active { background: var(--dark); color: #fff; border-color: var(--dark); }

    /* กล่องฟอร์มค้นหา + วันที่ */
    .search-row {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        align-items: center;
    }

    .search-input {
        padding: 10px 15px;
        border: 1px solid #ddd;
        border-radius: 8px;
        width: 250px;
        outline: none;
        font-family: inherit;
    }
    .search-input:focus { border-color: var(--primary); }

    .date-group {
        display: flex;
        align-items: center;
        gap: 10px;
        background: #fff;
        padding: 5px 15px;
        border-radius: 8px;
        border: 1px solid #ddd;
    }
    
    .date-input {
        border: none;
        outline: none;
        padding: 5px;
        color: var(--dark);
        font-family: inherit;
    }

    /* ตาราง */
    .admin-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 800px;
    }
    .table-responsive { overflow-x: auto; }
    
    .admin-table th { background: #f8f9fa; color: #555; padding: 16px; text-align: left; font-weight: 600; border-bottom: 2px solid #eee; }
    .admin-table td { padding: 16px; border-bottom: 1px solid #f0f0f0; vertical-align: middle; }
    .admin-table tbody tr:hover { background: #fafafa; }

    .order-number { font-weight: bold; color: var(--primary, #1f6feb); text-decoration: none; background: #eef2ff; padding: 6px 12px; border-radius: 8px; font-family: monospace; transition: background 0.2s; }
    .order-number:hover { background: #dce4ff; }

    .status-form { display: flex; align-items: center; gap: 8px; }
    .status-select { padding: 8px 12px; border: 1px solid #ddd; border-radius: 8px; outline: none; font-size: 14px; font-weight: bold; cursor: pointer; }
    .status-select.s-pending { border-color: #f39c12; color: #d35400; background: #fffdf5; }
    .status-select.s-paid { border-color: #27ae60; color: #27ae60; background: #f4fdf8; }
    .status-select.s-shipped { border-color: #1f6feb; color: #1f6feb; background: #f4f7ff; }
    .status-select.s-cancelled { border-color: #e74c3c; color: #e74c3c; background: #fff5f5; }

    .btn-update { background: var(--dark); color: #fff; border: none; padding: 8px 16px; border-radius: 8px; cursor: pointer; font-weight: bold; transition: transform 0.2s; }
    .btn-update:hover { transform: translateY(-2px); }

    .alert { padding: 15px 20px; border-radius: 12px; margin-bottom: 20px; font-weight: bold; display: flex; align-items: center; gap: 10px; }
    .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
</style>

<main style="max-width: 1200px; margin: 0 auto; padding: 20px;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="margin: 0; color: var(--dark); font-size: 28px;">⚙️ จัดการออเดอร์ (แอดมิน)</h2>
        <a href="admin_dashboard.php" style="color: var(--muted); text-decoration: none; font-weight: bold;">← กลับหน้าแดชบอร์ด</a>
    </div>

    <?php if(!empty($msg)): ?>
        <div class="alert alert-<?php echo $msg_type; ?>">
            <?php echo $msg_type === 'success' ? '✅' : '❌'; ?> 
            <?php echo htmlspecialchars($msg); ?>
        </div>
    <?php endif; ?>

    <div class="admin-container">
        
        <div class="toolbar">
            
            <div class="filter-tabs">
                <a href="?status=all&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>&search=<?php echo urlencode($search_query); ?>" class="filter-btn <?php echo $filter_status === 'all' ? 'active' : ''; ?>">ทั้งหมด</a>
                <a href="?status=pending&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>&search=<?php echo urlencode($search_query); ?>" class="filter-btn <?php echo $filter_status === 'pending' ? 'active' : ''; ?>">รอดำเนินการ</a>
                <a href="?status=paid&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>&search=<?php echo urlencode($search_query); ?>" class="filter-btn <?php echo $filter_status === 'paid' ? 'active' : ''; ?>">ชำระเงินแล้ว</a>
                <a href="?status=shipped&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>&search=<?php echo urlencode($search_query); ?>" class="filter-btn <?php echo $filter_status === 'shipped' ? 'active' : ''; ?>">จัดส่งแล้ว</a>
            </div>

            <form method="GET" class="search-row">
                <input type="hidden" name="status" value="<?php echo htmlspecialchars($filter_status); ?>">
                
                <input type="text" name="search" class="search-input" placeholder="ค้นหาเลขที่ออเดอร์ หรือ ชื่อลูกค้า..." value="<?php echo htmlspecialchars($search_query); ?>">
                
                <div class="date-group">
                    <span style="color: var(--muted); font-size: 14px;">📅 ตั้งแต่:</span>
                    <input type="date" name="start_date" class="date-input" value="<?php echo htmlspecialchars($start_date); ?>">
                    
                    <span style="color: var(--muted); font-size: 14px;">ถึง:</span>
                    <input type="date" name="end_date" class="date-input" value="<?php echo htmlspecialchars($end_date); ?>">
                </div>

                <button type="submit" class="btn" style="padding: 10px 20px; border-radius: 8px;">ค้นหา</button>
                
                <?php if(!empty($search_query) || !empty($start_date) || !empty($end_date)): ?>
                    <a href="admin_orders.php" class="btn secondary" style="padding: 10px 15px; border-radius: 8px;">ล้างค่า</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ออเดอร์</th>
                        <th>ลูกค้า</th>
                        <th>ยอดรวม</th>
                        <th>วันที่สั่งซื้อ</th>
                        <th style="width: 250px;">จัดการสถานะ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($res->num_rows > 0): ?>
                        <?php while($row = $res->fetch_assoc()): 
                            $date_str = date('Ymd', strtotime($row['created_at']));
                            $realistic_order_id = sprintf("ORD-%s-%04d", $date_str, $row['id']);
                        ?>
                            <tr>
                                <td>
                                    <a href="order_details.php?id=<?php echo $row['id']; ?>" class="order-number">
                                        <?php echo $realistic_order_id; ?>
                                    </a>
                                </td>
                                <td style="font-weight: 500;">👤 <?php echo htmlspecialchars($row['username']); ?></td>
                                <td style="font-weight: bold;">฿ <?php echo number_format($row['total_amount'], 2); ?></td>
                                <td style="color: #666;"><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                                
                                <td>
                                    <form method="post" class="status-form">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                                        
                                        <select name="status" class="status-select s-<?php echo $row['status']; ?>">
                                            <option value="pending" <?php if($row['status']=='pending') echo 'selected'; ?>>⏳ รอดำเนินการ</option>
                                            <option value="paid" <?php if($row['status']=='paid') echo 'selected'; ?>>✅ ชำระเงินแล้ว</option>
                                            <option value="shipped" <?php if($row['status']=='shipped') echo 'selected'; ?>>🚚 จัดส่งแล้ว</option>
                                            <option value="cancelled" <?php if($row['status']=='cancelled') echo 'selected'; ?>>❌ ยกเลิก</option>
                                        </select>
                                        
                                        <button type="submit" class="btn-update">อัปเดต</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 40px; color: var(--muted);">
                                🔍 ไม่พบออเดอร์ในช่วงเวลาที่คุณค้นหา หรือยังไม่มีออเดอร์ในสถานะนี้
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</main>

<?php include_once __DIR__.'/footer.php'; ?>