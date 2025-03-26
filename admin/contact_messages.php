<?php
require_once '../includes/admin_auth.php';
require_once '../includes/functions.php';

// بررسی سطح دسترسی
if (!hasPermission('admin')) {
    header('Location: /admin/');
    exit();
}

// پارامترهای صفحه‌بندی
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// فیلترها
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? 'all';

// دریافت پیام‌ها از دیتابیس
try {
    $pdo = db_connect();
    
    $where = [];
    $params = [];
    
    if (!empty($search)) {
        $where[] = "(name LIKE :search OR email LIKE :search OR subject LIKE :search)";
        $params[':search'] = "%$search%";
    }
    
    if ($status === 'unread') {
        $where[] = "is_read = 0";
    } elseif ($status === 'read') {
        $where[] = "is_read = 1";
    }
    
    $where_clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
    
    // تعداد کل پیام‌ها
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM contact_messages $where_clause");
    $stmt->execute($params);
    $total_messages = $stmt->fetchColumn();
    
    $total_pages = ceil($total_messages / $per_page);
    
    // دریافت پیام‌ها
    $stmt = $pdo->prepare("
        SELECT id, name, email, subject, created_at, is_read 
        FROM contact_messages 
        $where_clause 
        ORDER BY created_at DESC 
        LIMIT :offset, :per_page
    ");
    
    $params[':offset'] = $offset;
    $params[':per_page'] = $per_page;
    
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    
    $stmt->execute();
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("خطا در ارتباط با پایگاه داده: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مدیریت پیام‌های تماس</title>
    <link rel="stylesheet" href="/assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include '../includes/admin_header.php'; ?>
    
    <div class="admin-container">
        <?php include '../includes/admin_sidebar.php'; ?>
        
        <main class="admin-content">
            <div class="content-header">
                <h1>پیام‌های تماس</h1>
                <div class="header-actions">
                    <div class="search-box">
                        <form method="GET" action="">
                            <input type="text" name="search" placeholder="جستجو..." value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit"><i class="fas fa-search"></i></button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="filters">
                <div class="filter-tabs">
                    <a href="?status=all" class="<?php echo $status === 'all' ? 'active' : ''; ?>">همه پیام‌ها (<?php echo $total_messages; ?>)</a>
                    <a href="?status=unread" class="<?php echo $status === 'unread' ? 'active' : ''; ?>">خوانده نشده</a>
                    <a href="?status=read" class="<?php echo $status === 'read' ? 'active' : ''; ?>">خوانده شده</a>
                </div>
            </div>
            
            <div class="card">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>نام</th>
                                <th>ایمیل</th>
                                <th>موضوع</th>
                                <th>تاریخ</th>
                                <th>وضعیت</th>
                                <th>عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($messages)): ?>
                                <tr>
                                    <td colspan="6" class="empty-table">پیامی یافت نشد</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($messages as $message): ?>
                                    <tr class="<?php echo $message['is_read'] ? '' : 'unread'; ?>">
                                        <td><?php echo htmlspecialchars($message['name']); ?></td>
                                        <td><?php echo htmlspecialchars($message['email']); ?></td>
                                        <td><?php echo htmlspecialchars($message['subject']); ?></td>
                                        <td><?php echo jdate('Y/m/d H:i', strtotime($message['created_at'])); ?></td>
                                        <td>
                                            <span class="status-badge <?php echo $message['is_read'] ? 'read' : 'unread'; ?>">
                                                <?php echo $message['is_read'] ? 'خوانده شده' : 'خوانده نشده'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="actions">
                                                <a href="view_message.php?id=<?php echo $message['id']; ?>" class="btn btn-sm btn-primary" title="مشاهده">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="delete_message.php?id=<?php echo $message['id']; ?>" class="btn btn-sm btn-danger" title="حذف" onclick="return confirm('آیا از حذف این پیام مطمئن هستید؟')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status; ?>" class="page-link">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status; ?>" class="page-link <?php echo $i === $page ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status; ?>" class="page-link">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <script src="/assets/js/admin.js"></script>
</body>
</html>
