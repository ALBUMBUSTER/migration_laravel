<?php
$page_title = "System Logs";
require_once '../config/auth.php';
require_once '../config/connection.php';
require_once '../config/functions.php';

Auth::checkAuth();
Auth::checkRole(['admin']);

$database = new Database();
$db = $database->getConnection();

// Filter parameters
$user_id = $_GET['user_id'] ?? '';
$action = $_GET['action'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 50;
$offset = ($page - 1) * $limit;

// Build query
$query = "SELECT al.*, u.username, u.full_name, u.role 
          FROM activity_logs al 
          JOIN users u ON al.user_id = u.id 
          WHERE 1=1";
$count_query = "SELECT COUNT(*) as total 
                FROM activity_logs al 
                JOIN users u ON al.user_id = u.id 
                WHERE 1=1";
$params = [];
$count_params = [];

if (!empty($user_id)) {
    $query .= " AND al.user_id = :user_id";
    $count_query .= " AND al.user_id = :user_id";
    $params[':user_id'] = $user_id;
    $count_params[':user_id'] = $user_id;
}

if (!empty($action)) {
    $query .= " AND al.action LIKE :action";
    $count_query .= " AND al.action LIKE :action";
    $params[':action'] = "%$action%";
    $count_params[':action'] = "%$action%";
}

if (!empty($date_from)) {
    $query .= " AND DATE(al.created_at) >= :date_from";
    $count_query .= " AND DATE(al.created_at) >= :date_from";
    $params[':date_from'] = $date_from;
    $count_params[':date_from'] = $date_from;
}

if (!empty($date_to)) {
    $query .= " AND DATE(al.created_at) <= :date_to";
    $count_query .= " AND DATE(al.created_at) <= :date_to";
    $params[':date_to'] = $date_to;
    $count_params[':date_to'] = $date_to;
}

$query .= " ORDER BY al.created_at DESC LIMIT :limit OFFSET :offset";

// Get total count
$count_stmt = $db->prepare($count_query);
foreach ($count_params as $key => $value) {
    $count_stmt->bindValue($key, $value);
}
$count_stmt->execute();
$total_result = $count_stmt->fetch(PDO::FETCH_ASSOC);
$total_logs = $total_result['total'];
$total_pages = ceil($total_logs / $limit);

// Get logs
$stmt = $db->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get users for filter dropdown
$users_query = "SELECT id, username, full_name FROM users ORDER BY full_name";
$users_stmt = $db->prepare($users_query);
$users_stmt->execute();
$all_users = $users_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get unique actions for filter
$actions_query = "SELECT DISTINCT action FROM activity_logs ORDER BY action";
$actions_stmt = $db->prepare($actions_query);
$actions_stmt->execute();
$all_actions = $actions_stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/topbar.php'; ?>

<div class="main-container">
    <?php include '../includes/sidebar.php'; ?>
    
    <main class="content">
        <div class="page-header">
            <div class="page-title">
                <h1>System Activity Logs</h1>
                <p>Monitor all user activities and system events</p>
            </div>
            <div class="page-actions">
                <button onclick="exportLogs()" class="btn btn-primary">Export Logs</button>
                <?php if ($total_logs > 1000): ?>
                    <button onclick="cleanOldLogs()" class="btn btn-warning">Clean Old Logs</button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Filters -->
        <div class="form-container">
            <h3>Filter Logs</h3>
            <form method="GET" class="filter-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="user_id">User</label>
                        <select id="user_id" name="user_id">
                            <option value="">All Users</option>
                            <?php foreach ($all_users as $user): ?>
                                <option value="<?php echo $user['id']; ?>" <?php echo $user_id == $user['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($user['full_name'] . ' (' . $user['username'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="action">Action Type</label>
                        <select id="action" name="action">
                            <option value="">All Actions</option>
                            <?php foreach ($all_actions as $action_item): ?>
                                <option value="<?php echo $action_item; ?>" <?php echo $action == $action_item ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($action_item); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="date_from">From Date</label>
                        <input type="date" id="date_from" name="date_from" value="<?php echo $date_from; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="date_to">To Date</label>
                        <input type="date" id="date_to" name="date_to" value="<?php echo $date_to; ?>">
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="system_logs.php" class="btn btn-outline">Reset</a>
                </div>
            </form>
        </div>

        <!-- Summary Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Logs</h3>
                <div class="stat-value"><?php echo number_format($total_logs); ?></div>
            </div>
            
            <div class="stat-card">
                <h3>Logs Today</h3>
                <div class="stat-value">
                    <?php 
                    $today_query = "SELECT COUNT(*) as count FROM activity_logs WHERE DATE(created_at) = CURDATE()";
                    $today_stmt = $db->prepare($today_query);
                    $today_stmt->execute();
                    $today_count = $today_stmt->fetch(PDO::FETCH_ASSOC)['count'];
                    echo number_format($today_count);
                    ?>
                </div>
            </div>
            
            <div class="stat-card">
                <h3>Unique Users</h3>
                <div class="stat-value">
                    <?php 
                    $unique_query = "SELECT COUNT(DISTINCT user_id) as count FROM activity_logs";
                    $unique_stmt = $db->prepare($unique_query);
                    $unique_stmt->execute();
                    $unique_count = $unique_stmt->fetch(PDO::FETCH_ASSOC)['count'];
                    echo number_format($unique_count);
                    ?>
                </div>
            </div>
            
            <div class="stat-card">
                <h3>Most Active User</h3>
                <div class="stat-value">
                    <?php 
                    $active_query = "SELECT u.username, COUNT(al.id) as count 
                                    FROM activity_logs al 
                                    JOIN users u ON al.user_id = u.id 
                                    GROUP BY al.user_id 
                                    ORDER BY count DESC 
                                    LIMIT 1";
                    $active_stmt = $db->prepare($active_query);
                    $active_stmt->execute();
                    $most_active = $active_stmt->fetch(PDO::FETCH_ASSOC);
                    echo $most_active ? htmlspecialchars($most_active['username']) : 'N/A';
                    ?>
                </div>
            </div>
        </div>

        <!-- Activity Logs Table -->
        <div class="data-table">
            <div class="table-header">
                <h3>Activity Logs (<?php echo number_format($total_logs); ?> total)</h3>
                <div class="table-actions">
                    <span class="badge">Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>User</th>
                        <th>Role</th>
                        <th>Action</th>
                        <th>Description</th>
                        <th>IP Address</th>
                        <th>User Agent</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($logs) > 0): ?>
                        <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?php echo formatDateTime($log['created_at']); ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($log['full_name']); ?></strong><br>
                                <small><?php echo htmlspecialchars($log['username']); ?></small>
                            </td>
                            <td><span class="status"><?php echo ucfirst($log['role']); ?></span></td>
                            <td>
                                <span class="log-action <?php echo getActionClass($log['action']); ?>">
                                    <?php echo htmlspecialchars($log['action']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($log['description']); ?></td>
                            <td><code><?php echo htmlspecialchars($log['ip_address']); ?></code></td>
                            <td class="user-agent">
                                <small><?php echo htmlspecialchars(substr($log['user_agent'], 0, 50)); ?>...</small>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: #64748b;">
                                No activity logs found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=1<?php echo buildQueryString(['page' => 1]); ?>" class="btn btn-outline">First</a>
                    <a href="?page=<?php echo $page - 1; ?><?php echo buildQueryString(['page' => $page - 1]); ?>" class="btn btn-outline">Previous</a>
                <?php endif; ?>
                
                <span class="page-info">Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?><?php echo buildQueryString(['page' => $page + 1]); ?>" class="btn btn-outline">Next</a>
                    <a href="?page=<?php echo $total_pages; ?><?php echo buildQueryString(['page' => $total_pages]); ?>" class="btn btn-outline">Last</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<style>
.log-action {
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
    font-size: 0.85rem;
    font-weight: 500;
}

.log-action.login { background: #d1fae5; color: #065f46; }
.log-action.logout { background: #fee2e2; color: #991b1b; }
.log-action.add { background: #dbeafe; color: #1e40af; }
.log-action.edit { background: #fef3c7; color: #92400e; }
.log-action.delete { background: #f3e8ff; color: #6b21a8; }
.log-action.approve { background: #dcfce7; color: #166534; }
.log-action.reject { background: #fecaca; color: #dc2626; }

.user-agent {
    max-width: 200px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem;
    border-top: 1px solid #e2e8f0;
}

.page-info {
    padding: 0.5rem 1rem;
    color: var(--secondary);
}
</style>

<script>
function exportLogs() {
    let params = new URLSearchParams(window.location.search);
    window.open('export_logs.php?' + params.toString(), '_blank');
}

function cleanOldLogs() {
    if (confirm('This will delete logs older than 90 days. Continue?')) {
        fetch('clean_logs.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Old logs cleaned successfully!');
                    location.reload();
                } else {
                    alert('Error cleaning logs: ' + data.message);
                }
            });
    }
}
</script>

<?php
// Helper functions
function getActionClass($action) {
    $action_lower = strtolower($action);
    if (strpos($action_lower, 'login') !== false) return 'login';
    if (strpos($action_lower, 'logout') !== false) return 'logout';
    if (strpos($action_lower, 'add') !== false) return 'add';
    if (strpos($action_lower, 'edit') !== false) return 'edit';
    if (strpos($action_lower, 'delete') !== false) return 'delete';
    if (strpos($action_lower, 'approve') !== false) return 'approve';
    if (strpos($action_lower, 'reject') !== false) return 'reject';
    return '';
}

function buildQueryString($new_params = []) {
    $params = $_GET;
    unset($params['page']);
    
    foreach ($new_params as $key => $value) {
        $params[$key] = $value;
    }
    
    return '&' . http_build_query($params);
}
?>

<?php include '../includes/footer.php'; ?>