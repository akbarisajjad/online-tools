<?php
class Database {
    private static $instance = null;
    private $pdo;
    
    private function __construct() {
        try {
            $this->pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            logError("Connection failed: " . $e->getMessage());
            throw $e;
        }
    }
    
    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    public function query($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }
}

class ContactQuery {
    private $db;
    private $query;
    private $params = [];
    private $joins = [];
    private $wheres = [];
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->query = "SELECT 
            m.id, m.name, m.email, m.subject, m.message, 
            m.ip_address, m.created_at, m.is_read, m.status,
            m.priority, m.assigned_to,
            c.id AS category_id, c.name AS category_name, c.color AS category_color,
            u.full_name AS assigned_to_name, u.avatar AS assigned_to_avatar
            FROM contact_messages m
            LEFT JOIN contact_categories c ON m.category_id = c.id
            LEFT JOIN users u ON m.assigned_to = u.id";
    }
    
    public function search($keyword) {
        $this->wheres[] = "(m.subject LIKE :keyword OR m.message LIKE :keyword OR m.name LIKE :keyword OR m.email LIKE :keyword)";
        $this->params[':keyword'] = "%$keyword%";
        return $this;
    }
    
    public function filterByCategory($category_id) {
        $this->wheres[] = "m.category_id = :category_id";
        $this->params[':category_id'] = $category_id;
        return $this;
    }
    
    public function filterByPriority($priority) {
        $this->wheres[] = "m.priority = :priority";
        $this->params[':priority'] = $priority;
        return $this;
    }
    
    public function filterByStatus($status) {
        if ($status === 'unread') {
            $this->wheres[] = "m.is_read = 0";
        } elseif ($status === 'read') {
            $this->wheres[] = "m.is_read = 1";
        } elseif ($status === 'closed') {
            $this->wheres[] = "m.status = 'closed'";
        }
        return $this;
    }
    
    public function filterByAssignedUser($user_id) {
        $this->wheres[] = "m.assigned_to = :assigned_to";
        $this->params[':assigned_to'] = $user_id;
        return $this;
    }
    
    public function filterByDateFrom($date) {
        $this->wheres[] = "m.created_at >= :date_from";
        $this->params[':date_from'] = $date . ' 00:00:00';
        return $this;
    }
    
    public function filterByDateTo($date) {
        $this->wheres[] = "m.created_at <= :date_to";
        $this->params[':date_to'] = $date . ' 23:59:59';
        return $this;
    }
    
    public function sort($sort) {
        switch ($sort) {
            case 'oldest':
                $this->query .= " ORDER BY m.created_at ASC";
                break;
            case 'priority_high':
                $this->query .= " ORDER BY 
                    CASE m.priority 
                        WHEN 'high' THEN 1 
                        WHEN 'normal' THEN 2 
                        WHEN 'low' THEN 3 
                        ELSE 4 
                    END, m.created_at DESC";
                break;
            case 'priority_low':
                $this->query .= " ORDER BY 
                    CASE m.priority 
                        WHEN 'low' THEN 1 
                        WHEN 'normal' THEN 2 
                        WHEN 'high' THEN 3 
                        ELSE 4 
                    END, m.created_at DESC";
                break;
            default: // newest
                $this->query .= " ORDER BY m.created_at DESC";
        }
        return $this;
    }
    
    public function paginate($per_page, $offset) {
        $this->query .= " LIMIT :offset, :per_page";
        $this->params[':offset'] = $offset;
        $this->params[':per_page'] = $per_page;
        return $this;
    }
    
    public function get() {
        $sql = $this->query;
        
        if (!empty($this->wheres)) {
            $sql .= " WHERE " . implode(" AND ", $this->wheres);
        }
        
        $stmt = $this->db->query($sql, $this->params);
        return $stmt->fetchAll();
    }
    
    public function count() {
        $sql = "SELECT COUNT(*) FROM contact_messages m";
        
        if (!empty($this->wheres)) {
            $sql .= " WHERE " . implode(" AND ", $this->wheres);
        }
        
        $stmt = $this->db->query($sql, $this->params);
        return $stmt->fetchColumn();
    }
}

// توابع کمکی
function getPriorityLabel($priority) {
    switch ($priority) {
        case 'high': return 'فوری';
        case 'low': return 'غیرفوری';
        default: return 'عادی';
    }
}

function getStatusLabel($status) {
    switch ($status) {
        case 'closed': return 'بسته شده';
        case 'pending': return 'در انتظار';
        case 'replied': return 'پاسخ داده شده';
        default: return $status;
    }
}

function buildPaginationUrl($page, $params) {
    $params['page'] = $page;
    return 'contact_messages.php?' . http_build_query($params);
}

function getUserAvatar($avatar) {
    if ($avatar && file_exists(UPLOAD_DIR . '/avatars/' . $avatar)) {
        return UPLOAD_URL . '/avatars/' . $avatar;
    }
    return '/assets/images/default-avatar.png';
}
?>
