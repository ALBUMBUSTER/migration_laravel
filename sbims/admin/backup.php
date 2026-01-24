<?php
$page_title = "Backup & Restore";
require_once '../config/auth.php';
require_once '../config/connection.php';
require_once '../config/functions.php';

Auth::checkAuth();
Auth::checkRole(['admin']);

$database = new Database();
$backup_dir = '../backups/';
$backup_files = [];

// Create backup directory if it doesn't exist
if (!file_exists($backup_dir)) {
    mkdir($backup_dir, 0755, true);
}

// Get list of backup files
if (is_dir($backup_dir)) {
    $files = scandir($backup_dir);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..' && pathinfo($file, PATHINFO_EXTENSION) == 'sql') {
            $file_path = $backup_dir . $file;
            $backup_files[] = [
                'name' => $file,
                'path' => $file_path,
                'size' => filesize($file_path),
                'modified' => filemtime($file_path)
            ];
        }
    }
    
    // Sort by modification time (newest first)
    usort($backup_files, function($a, $b) {
        return $b['modified'] - $a['modified'];
    });
}

// Handle backup action
if (isset($_POST['action']) && $_POST['action'] == 'backup') {
    $backup_name = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
    $backup_path = $backup_dir . $backup_name;
    
    if (createDatabaseBackup($backup_path)) {
        $_SESSION['success'] = "Database backup created successfully: " . $backup_name;
        // Log activity
        Auth::logActivity($_SESSION['user_id'], 'Create Backup', "Created database backup: $backup_name");
    } else {
        $_SESSION['error'] = "Failed to create database backup.";
    }
    
    header("Location: backup.php");
    exit();
}

// Handle restore action
if (isset($_POST['action']) && $_POST['action'] == 'restore' && isset($_POST['backup_file'])) {
    $backup_file = $_POST['backup_file'];
    $backup_path = $backup_dir . $backup_file;
    
    if (file_exists($backup_path)) {
        if (restoreDatabaseBackup($backup_path)) {
            $_SESSION['success'] = "Database restored successfully from: " . $backup_file;
            // Log activity
            Auth::logActivity($_SESSION['user_id'], 'Restore Backup', "Restored database from: $backup_file");
        } else {
            $_SESSION['error'] = "Failed to restore database backup.";
        }
    } else {
        $_SESSION['error'] = "Backup file not found.";
    }
    
    header("Location: backup.php");
    exit();
}

// Handle delete action
if (isset($_GET['delete']) && isset($_GET['file'])) {
    $file_to_delete = $backup_dir . $_GET['file'];
    
    if (file_exists($file_to_delete) && unlink($file_to_delete)) {
        $_SESSION['success'] = "Backup file deleted successfully.";
        // Log activity
        Auth::logActivity($_SESSION['user_id'], 'Delete Backup', "Deleted backup file: " . $_GET['file']);
    } else {
        $_SESSION['error'] = "Failed to delete backup file.";
    }
    
    header("Location: backup.php");
    exit();
}

// Database backup function
function createDatabaseBackup($backup_path) {
    $database = new Database();
    $db = $database->getConnection();
    
    try {
        // Get all table names
        $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        
        $backup_content = "-- SBIMS-PRO Database Backup\n";
        $backup_content .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        $backup_content .= "-- Database: sbims_pro\n\n";
        
        foreach ($tables as $table) {
            // Add drop table if exists
            $backup_content .= "DROP TABLE IF EXISTS `$table`;\n\n";
            
            // Get create table statement
            $create_table = $db->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC);
            $backup_content .= $create_table['Create Table'] . ";\n\n";
            
            // Get table data
            $rows = $db->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($rows) > 0) {
                $backup_content .= "INSERT INTO `$table` VALUES\n";
                $insert_values = [];
                
                foreach ($rows as $row) {
                    $values = array_map(function($value) use ($db) {
                        if ($value === null) return 'NULL';
                        return $db->quote($value);
                    }, array_values($row));
                    
                    $insert_values[] = "(" . implode(', ', $values) . ")";
                }
                
                $backup_content .= implode(",\n", $insert_values) . ";\n\n";
            }
        }
        
        // Write to file
        return file_put_contents($backup_path, $backup_content) !== false;
        
    } catch (Exception $e) {
        error_log("Backup error: " . $e->getMessage());
        return false;
    }
}

// Database restore function
function restoreDatabaseBackup($backup_path) {
    $database = new Database();
    $db = $database->getConnection();
    
    try {
        // Read backup file
        $backup_content = file_get_contents($backup_path);
        
        // Split by semicolon (end of each SQL statement)
        $queries = explode(";\n", $backup_content);
        
        // Execute each query
        foreach ($queries as $query) {
            $query = trim($query);
            if (!empty($query)) {
                $db->exec($query);
            }
        }
        
        return true;
    } catch (Exception $e) {
        error_log("Restore error: " . $e->getMessage());
        return false;
    }
}
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/topbar.php'; ?>

<div class="main-container">
    <?php include '../includes/sidebar.php'; ?>
    
    <main class="content">
        <div class="page-header">
            <div class="page-title">
                <h1>Database Backup & Restore</h1>
                <p>Manage system backups and database restoration</p>
            </div>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <!-- Create Backup Section -->
        <div class="form-container">
            <h3>Create New Backup</h3>
            <div class="backup-info">
                <p><strong>⚠️ Important:</strong> Create regular backups to prevent data loss.</p>
                <p>Backups include all database tables and data.</p>
            </div>
            
            <form method="POST">
                <input type="hidden" name="action" value="backup">
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary" onclick="return confirm('Create database backup now?')">
                        Create Backup Now
                    </button>
                    <button type="button" class="btn btn-outline" onclick="scheduleBackup()">Schedule Backup</button>
                </div>
            </form>
        </div>

        <!-- Backup Files List -->
        <div class="data-table">
            <div class="table-header">
                <h3>Available Backups</h3>
                <div class="table-actions">
                    <span class="badge"><?php echo count($backup_files); ?> backup files</span>
                </div>
            </div>

            <?php if (count($backup_files) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Backup File</th>
                            <th>Size</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($backup_files as $backup): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($backup['name']); ?></strong><br>
                                <small><?php echo realpath($backup['path']); ?></small>
                            </td>
                            <td><?php echo formatSize($backup['size']); ?></td>
                            <td><?php echo date('M j, Y g:i A', $backup['modified']); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="restore">
                                        <input type="hidden" name="backup_file" value="<?php echo $backup['name']; ?>">
                                        <button type="submit" class="btn btn-sm btn-success" 
                                                onclick="return confirm('WARNING: This will overwrite current database. Continue?')">
                                            Restore
                                        </button>
                                    </form>
                                    
                                    <a href="backup.php?download=<?php echo urlencode($backup['name']); ?>" 
                                       class="btn btn-sm btn-outline">Download</a>
                                       
                                    <a href="backup.php?delete=1&file=<?php echo urlencode($backup['name']); ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Delete this backup file?')">Delete</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-data">
                    <p>No backup files found. Create your first backup above.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Backup Schedule Settings -->
        <div class="form-container">
            <h3>Backup Schedule Settings</h3>
            <form method="POST" id="scheduleForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="schedule_type">Schedule Type</label>
                        <select id="schedule_type" name="schedule_type">
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                            <option value="manual">Manual Only</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="backup_time">Backup Time (Daily)</label>
                        <input type="time" id="backup_time" name="backup_time" value="02:00">
                    </div>
                    
                    <div class="form-group">
                        <label for="retention_days">Retention Period</label>
                        <select id="retention_days" name="retention_days">
                            <option value="7">7 days</option>
                            <option value="30" selected>30 days</option>
                            <option value="90">90 days</option>
                            <option value="365">1 year</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save Schedule</button>
                </div>
            </form>
        </div>

        <!-- System Information -->
<div class="info-box">
    <h4>📊 Database Information</h4>
    <?php
    // Create a new database instance and get connection
    $database = new Database();
    $db = $database->getConnection();
    
    try {
        $db_info = $db->query("SELECT 
            (SELECT COUNT(*) FROM users) as user_count,
            (SELECT COUNT(*) FROM residents) as resident_count,
            (SELECT COUNT(*) FROM blotters) as blotter_count,
            (SELECT COUNT(*) FROM certificates) as certificate_count,
            (SELECT COUNT(*) FROM activity_logs) as log_count,
            (SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) 
             FROM information_schema.tables 
             WHERE table_schema = DATABASE()) as db_size_mb")->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        // Handle error gracefully
        $db_info = [
            'db_size_mb' => 'N/A',
            'user_count' => 'N/A',
            'resident_count' => 'N/A',
            'blotter_count' => 'N/A',
            'certificate_count' => 'N/A',
            'log_count' => 'N/A'
        ];
        error_log("Database info error: " . $e->getMessage());
    }
    ?>
    <div class="info-grid">
        <div class="info-item">
            <label>Database Size:</label>
            <span><?php echo $db_info['db_size_mb']; ?> MB</span>
        </div>
        <div class="info-item">
            <label>Total Users:</label>
            <span><?php echo $db_info['user_count']; ?></span>
        </div>
        <div class="info-item">
            <label>Total Residents:</label>
            <span><?php echo $db_info['resident_count']; ?></span>
        </div>
        <div class="info-item">
            <label>Total Blotters:</label>
            <span><?php echo $db_info['blotter_count']; ?></span>
        </div>
        <div class="info-item">
            <label>Total Certificates:</label>
            <span><?php echo $db_info['certificate_count']; ?></span>
        </div>
        <div class="info-item">
            <label>Total Logs:</label>
            <span><?php echo $db_info['log_count']; ?></span>
        </div>
    </div>
    <p><small>Last backup: <?php echo count($backup_files) > 0 ? date('M j, Y g:i A', $backup_files[0]['modified']) : 'Never'; ?></small></p>
</div>
    </main>
</div>

<style>
.backup-info {
    background: #f0f9ff;
    border: 1px solid #7dd3fc;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
}

.backup-info p {
    margin: 0.5rem 0;
    color: #0369a1;
}

.action-buttons {
    display: flex;
    gap: 0.3rem;
    flex-wrap: wrap;
}

.info-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 1.5rem;
    margin-top: 2rem;
}

.info-box h4 {
    color: var(--dark);
    margin-bottom: 1rem;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1rem;
}
</style>

<script>
function scheduleBackup() {
    if (confirm('Schedule automatic daily backups at 2:00 AM?')) {
        // In a real implementation, this would set up a cron job
        alert('Backup schedule would be configured here. In production, this would set up a cron job.');
    }
}

function formatSize(bytes) {
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    if (bytes === 0) return '0 Byte';
    const i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
    return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
}

// Handle file download
<?php if (isset($_GET['download'])): ?>
window.onload = function() {
    const file = "<?php echo $_GET['download']; ?>";
    window.location.href = '../backups/' + file;
};
<?php endif; ?>
</script>

<?php
// Helper function to format file size
function formatSize($bytes) {
    if ($bytes >= 1073741824) {
        $bytes = number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        $bytes = number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        $bytes = number_format($bytes / 1024, 2) . ' KB';
    } elseif ($bytes > 1) {
        $bytes = $bytes . ' bytes';
    } elseif ($bytes == 1) {
        $bytes = $bytes . ' byte';
    } else {
        $bytes = '0 bytes';
    }
    return $bytes;
}
?>

<?php include '../includes/footer.php'; ?>