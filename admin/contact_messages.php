<?php
require_once '../includes/admin_auth.php';
require_once '../includes/functions.php';
require_once '../includes/db_advanced.php';

// بررسی سطح دسترسی
if (!hasPermission('contact_manager')) {
    setFlashMessage('دسترسی غیرمجاز', 'error');
    header('Location: /admin/dashboard.php');
    exit();
}

// پارامترهای جستجوی پیشرفته
$search_params = [
    'keyword' => $_GET['keyword'] ?? '',
    'category' => $_GET['category'] ?? '',
    'priority' => $_GET['priority'] ?? '',
    'status' => $_GET['status'] ?? 'all',
    'assigned_to' => $_GET['assigned_to'] ?? '',
    'date_from' => $_GET['date_from'] ?? '',
    'date_to' => $_GET['date_to'] ?? '',
    'sort' => $_GET['sort'] ?? 'newest'
];

// صفحه‌بندی
$current_page = max(1, $_GET['page'] ?? 1);
$per_page = 15;
$offset = ($current_page - 1) * $per_page;

// دریافت پیام‌ها با فیلترهای پیشرفته
try {
    $db = Database::getInstance();
    $query = new ContactQuery();
    
    // اعمال فیلترها
    if (!empty($search_params['keyword'])) {
        $query->search($search_params['keyword']);
    }
    
    if (!empty($search_params['category'])) {
        $query->filterByCategory($search_params['category']);
    }
    
    if (!empty($search_params['priority'])) {
        $query->filterByPriority($search_params['priority']);
    }
    
    if ($search_params['status'] !== 'all') {
        $query->filterByStatus($search_params['status']);
    }
    
    if (!empty($search_params['assigned_to'])) {
        $query->filterByAssignedUser($search_params['assigned_to']);
    }
    
    if (!empty($search_params['date_from'])) {
        $query->filterByDateFrom($search_params['date_from']);
    }
    
    if (!empty($search_params['date_to'])) {
        $query->filterByDateTo($search_params['date_to']);
    }
    
    // مرتب‌سازی
    $query->sort($search_params['sort']);
    
    // دریافت نتایج
    $total_messages = $query->count();
    $messages = $query->paginate($per_page, $offset)->get();
    
    $total_pages = ceil($total_messages / $per_page);
    
    // دریافت لیست کارشناسان برای فیلتر
    $experts = $db->query("SELECT id, full_name FROM users WHERE role IN ('admin', 'contact_manager')")->fetchAll();
    
    // دریافت دسته‌بندی‌ها
    $categories = $db->query("SELECT id, name FROM contact_categories WHERE is_active = 1")->fetchAll();
    
} catch (PDOException $e) {
    logError("خطا در دریافت پیام‌ها: " . $e->getMessage());
    setFlashMessage('خطا در دریافت اطلاعات', 'error');
    $messages = [];
    $total_messages = 0;
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مدیریت پیام‌ها - نسخه پیشرفته</title>
    <?php include '../includes/admin_head.php'; ?>
    <link rel="stylesheet" href="/assets/css/advanced-contact.css">
</head>
<body class="admin-panel">
    <?php include '../includes/admin_header.php'; ?>
    
    <div class="admin-container">
        <?php include '../includes/admin_sidebar.php'; ?>
        
        <main class="admin-content">
            <div class="content-header">
                <h1><i class="fas fa-envelope-open-text"></i> مدیریت پیام‌ها</h1>
                
                <div class="header-actions">
                    <button class="btn btn-primary" data-toggle="modal" data-target="#advancedSearchModal">
                        <i class="fas fa-search-plus"></i> جستجوی پیشرفته
                    </button>
                    
                    <div class="dropdown">
                        <button class="btn btn-secondary dropdown-toggle" data-toggle="dropdown">
                            <i class="fas fa-cog"></i> عملیات گروهی
                        </button>
                        <div class="dropdown-menu">
                            <a href="#" class="dropdown-item" id="markAsReadSelected">
                                <i class="fas fa-envelope-open"></i> علامت‌گذاری به عنوان خوانده شده
                            </a>
                            <a href="#" class="dropdown-item" id="assignToExpertSelected">
                                <i class="fas fa-user-tag"></i> ارجاع به کارشناس
                            </a>
                            <a href="#" class="dropdown-item" id="changeCategorySelected">
                                <i class="fas fa-tag"></i> تغییر دسته‌بندی
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item text-danger" id="deleteSelected">
                                <i class="fas fa-trash"></i> حذف انتخاب شده‌ها
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php include '../includes/flash_messages.php'; ?>
            
            <!-- فیلترهای سریع -->
            <div class="quick-filters">
                <div class="filter-tabs">
                    <a href="?status=all" class="<?= $search_params['status'] === 'all' ? 'active' : '' ?>">
                        همه پیام‌ها <span class="badge"><?= $total_messages ?></span>
                    </a>
                    <a href="?status=unread" class="<?= $search_params['status'] === 'unread' ? 'active' : '' ?>">
                        خوانده نشده <span class="badge"><?= $query->filterByStatus('unread')->count() ?></span>
                    </a>
                    <a href="?status=read" class="<?= $search_params['status'] === 'read' ? 'active' : '' ?>">
                        خوانده شده <span class="badge"><?= $query->filterByStatus('read')->count() ?></span>
                    </a>
                    <a href="?status=closed" class="<?= $search_params['status'] === 'closed' ? 'active' : '' ?>">
                        بسته شده <span class="badge"><?= $query->filterByStatus('closed')->count() ?></span>
                    </a>
                </div>
                
                <div class="sort-options">
                    <span>مرتب‌سازی:</span>
                    <select id="sortSelect" class="form-control-sm">
                        <option value="newest" <?= $search_params['sort'] === 'newest' ? 'selected' : '' ?>>جدیدترین</option>
                        <option value="oldest" <?= $search_params['sort'] === 'oldest' ? 'selected' : '' ?>>قدیمی‌ترین</option>
                        <option value="priority_high" <?= $search_params['sort'] === 'priority_high' ? 'selected' : '' ?>>اولویت بالا</option>
                        <option value="priority_low" <?= $search_params['sort'] === 'priority_low' ? 'selected' : '' ?>>اولویت پایین</option>
                    </select>
                </div>
            </div>
            
            <!-- جدول پیام‌ها -->
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-hover" id="messagesTable">
                        <thead>
                            <tr>
                                <th width="30"><input type="checkbox" id="selectAll"></th>
                                <th width="60">اولویت</th>
                                <th>موضوع / فرستنده</th>
                                <th width="120">دسته‌بندی</th>
                                <th width="150">ارجاع به</th>
                                <th width="120">تاریخ</th>
                                <th width="100">وضعیت</th>
                                <th width="120">عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($messages)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <div class="empty-state">
                                            <i class="fas fa-envelope-open-text fa-3x"></i>
                                            <h4>پیامی یافت نشد</h4>
                                            <p>هیچ پیامی با معیارهای جستجوی شما مطابقت ندارد.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($messages as $message): ?>
                                    <tr class="
                                        <?= $message['is_read'] ? '' : 'unread' ?>
                                        <?= $message['priority'] === 'high' ? 'priority-high' : ($message['priority'] === 'low' ? 'priority-low' : '') ?>
                                    ">
                                        <td><input type="checkbox" class="message-checkbox" value="<?= $message['id'] ?>"></td>
                                        <td>
                                            <span class="priority-badge priority-<?= $message['priority'] ?>">
                                                <?= getPriorityLabel($message['priority']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="message-title">
                                                <a href="view_message.php?id=<?= $message['id'] ?>">
                                                    <?= htmlspecialchars($message['subject']) ?>
                                                </a>
                                            </div>
                                            <div class="message-sender">
                                                <?= htmlspecialchars($message['name']) ?>
                                                <small>&lt;<?= htmlspecialchars($message['email']) ?>&gt;</small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="category-badge" style="background-color: <?= $message['category_color'] ?>">
                                                <?= htmlspecialchars($message['category_name']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($message['assigned_to_name']): ?>
                                                <div class="assigned-user">
                                                    <img src="<?= getUserAvatar($message['assigned_to_avatar']) ?>" 
                                                         alt="<?= htmlspecialchars($message['assigned_to_name']) ?>" 
                                                         class="user-avatar">
                                                    <?= htmlspecialchars($message['assigned_to_name']) ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted">ارجاع نشده</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="date-time">
                                                <div><?= jdate('Y/m/d', strtotime($message['created_at'])) ?></div>
                                                <small><?= jdate('H:i', strtotime($message['created_at'])) ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?= $message['status'] ?>">
                                                <?= getStatusLabel($message['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="view_message.php?id=<?= $message['id'] ?>" 
                                                   class="btn btn-outline-primary" 
                                                   title="مشاهده">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                
                                                <div class="dropdown">
                                                    <button class="btn btn-outline-secondary dropdown-toggle" 
                                                            data-toggle="dropdown">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-left">
                                                        <a href="reply_message.php?id=<?= $message['id'] ?>" 
                                                           class="dropdown-item">
                                                            <i class="fas fa-reply"></i> پاسخ
                                                        </a>
                                                        <a href="#" class="dropdown-item change-priority" 
                                                           data-id="<?= $message['id'] ?>">
                                                            <i class="fas fa-flag"></i> تغییر اولویت
                                                        </a>
                                                        <a href="#" class="dropdown-item assign-expert" 
                                                           data-id="<?= $message['id'] ?>">
                                                            <i class="fas fa-user-tag"></i> ارجاع به کارشناس
                                                        </a>
                                                        <div class="dropdown-divider"></div>
                                                        <a href="delete_message.php?id=<?= $message['id'] ?>" 
                                                           class="dropdown-item text-danger" 
                                                           onclick="return confirm('آیا از حذف این پیام مطمئن هستید؟')">
                                                            <i class="fas fa-trash"></i> حذف
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- صفحه‌بندی -->
                <?php if ($total_pages > 1): ?>
                    <div class="card-footer">
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center">
                                <?php if ($current_page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" 
                                           href="<?= buildPaginationUrl($current_page - 1, $search_params) ?>">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?= $i === $current_page ? 'active' : '' ?>">
                                        <a class="page-link" href="<?= buildPaginationUrl($i, $search_params) ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($current_page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" 
                                           href="<?= buildPaginationUrl($current_page + 1, $search_params) ?>">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <!-- مودال جستجوی پیشرفته -->
    <div class="modal fade" id="advancedSearchModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form method="GET" action="">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-search-plus"></i> جستجوی پیشرفته</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>کلمه کلیدی</label>
                                    <input type="text" name="keyword" class="form-control" 
                                           value="<?= htmlspecialchars($search_params['keyword']) ?>"
                                           placeholder="جستجو در موضوع، متن پیام، نام و ایمیل">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>دسته‌بندی</label>
                                    <select name="category" class="form-control">
                                        <option value="">همه دسته‌بندی‌ها</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?= $category['id'] ?>" 
                                                <?= $search_params['category'] == $category['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($category['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>اولویت</label>
                                    <select name="priority" class="form-control">
                                        <option value="">همه اولویت‌ها</option>
                                        <option value="high" <?= $search_params['priority'] === 'high' ? 'selected' : '' ?>>بالا</option>
                                        <option value="normal" <?= $search_params['priority'] === 'normal' ? 'selected' : '' ?>>متوسط</option>
                                        <option value="low" <?= $search_params['priority'] === 'low' ? 'selected' : '' ?>>پایین</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>وضعیت</label>
                                    <select name="status" class="form-control">
                                        <option value="all" <?= $search_params['status'] === 'all' ? 'selected' : '' ?>>همه وضعیت‌ها</option>
                                        <option value="unread" <?= $search_params['status'] === 'unread' ? 'selected' : '' ?>>خوانده نشده</option>
                                        <option value="read" <?= $search_params['status'] === 'read' ? 'selected' : '' ?>>خوانده شده</option>
                                        <option value="closed" <?= $search_params['status'] === 'closed' ? 'selected' : '' ?>>بسته شده</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>ارجاع به</label>
                                    <select name="assigned_to" class="form-control">
                                        <option value="">همه کارشناسان</option>
                                        <?php foreach ($experts as $expert): ?>
                                            <option value="<?= $expert['id'] ?>" 
                                                <?= $search_params['assigned_to'] == $expert['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($expert['full_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>از تاریخ</label>
                                    <input type="text" name="date_from" class="form-control datepicker" 
                                           value="<?= htmlspecialchars($search_params['date_from']) ?>"
                                           placeholder="YYYY/MM/DD" autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>تا تاریخ</label>
                                    <input type="text" name="date_to" class="form-control datepicker" 
                                           value="<?= htmlspecialchars($search_params['date_to']) ?>"
                                           placeholder="YYYY/MM/DD" autocomplete="off">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">انصراف</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> اعمال فیلترها
                        </button>
                        <a href="contact_messages.php" class="btn btn-outline-danger">
                            <i class="fas fa-times"></i> حذف فیلترها
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- مودال ارجاع به کارشناس -->
    <div class="modal fade" id="assignExpertModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="assignExpertForm" method="POST" action="ajax/assign_expert.php">
                    <input type="hidden" name="message_ids" id="assignMessageIds">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-user-tag"></i> ارجاع به کارشناس</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>انتخاب کارشناس</label>
                            <select name="expert_id" class="form-control" required>
                                <option value="">-- انتخاب کنید --</option>
                                <?php foreach ($experts as $expert): ?>
                                    <option value="<?= $expert['id'] ?>">
                                        <?= htmlspecialchars($expert['full_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>یادداشت (اختیاری)</label>
                            <textarea name="note" class="form-control" rows="3" 
                                      placeholder="توضیحات برای کارشناس..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">انصراف</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check"></i> تایید ارجاع
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- مودال تغییر اولویت -->
    <div class="modal fade" id="changePriorityModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="changePriorityForm" method="POST" action="ajax/change_priority.php">
                    <input type="hidden" name="message_ids" id="priorityMessageIds">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-flag"></i> تغییر اولویت</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>انتخاب اولویت</label>
                            <select name="priority" class="form-control" required>
                                <option value="high">بالا (فوری)</option>
                                <option value="normal" selected>متوسط (عادی)</option>
                                <option value="low">پایین (غیرفوری)</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">انصراف</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check"></i> تایید تغییر
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <?php include '../includes/admin_footer.php'; ?>
    
    <script src="/assets/js/advanced-contact.js"></script>
</body>
</html>
