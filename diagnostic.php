<?php
/**
 * Diagnostic Check
 * Helps identify server errors and configuration issues
 */

header('Content-Type: text/html; charset=utf-8');

// Disable error suppression
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnostic Check | Glo-CED India</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
        code { background: #f3f4f6; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-family: monospace; }
    </style>
</head>
<body class="bg-gray-100 py-12 px-4">

    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">
            <i class="fas fa-stethoscope text-blue-600 mr-2"></i>Diagnostic Check
        </h1>

        <!-- PHP Version -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <i class="fas fa-code text-blue-600"></i>PHP Information
            </h2>
            <div class="space-y-3 text-sm">
                <p><strong>PHP Version:</strong> <code><?php echo phpversion(); ?></code></p>
                <p><strong>Server Software:</strong> <code><?php echo $_SERVER['SERVER_SOFTWARE']; ?></code></p>
                <p><strong>Document Root:</strong> <code><?php echo $_SERVER['DOCUMENT_ROOT']; ?></code></p>
                <p><strong>Current File:</strong> <code><?php echo __FILE__; ?></code></p>
                <p><strong>Script Name:</strong> <code><?php echo $_SERVER['SCRIPT_NAME']; ?></code></p>
            </div>
        </div>

        <!-- File Permissions -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <i class="fas fa-lock text-yellow-600"></i>File Permissions
            </h2>
            <div class="space-y-3 text-sm">
                <?php
                $files = [
                    '../config/db_config.php' => 'Database Config',
                    './index.php' => 'Admin Index',
                    './admin_users.php' => 'Admin Users',
                    'admin_submissions.php' => 'Admin Submissions',
                    'admin_manage_users.php' => 'Manage Users',
                ];
                
                foreach ($files as $file => $label) {
                    $full_path = __DIR__ . '/' . $file;
                    $exists = file_exists($full_path);
                    $readable = $exists && is_readable($full_path);
                    $status = $readable ? 'text-green-600' : ($exists ? 'text-yellow-600' : 'text-red-600');
                    $icon = $readable ? 'fa-check-circle' : ($exists ? 'fa-exclamation-circle' : 'fa-times-circle');
                    
                    echo '<p><i class="fas ' . $icon . ' ' . $status . ' mr-2"></i>';
                    echo '<strong>' . $label . ':</strong> ';
                    echo $readable ? '<span class="text-green-600">✓ OK</span>' : ($exists ? '<span class="text-yellow-600">⚠ Exists but not readable</span>' : '<span class="text-red-600">✗ Not found</span>');
                    echo ' <code class="text-xs">' . htmlspecialchars($file) . '</code>';
                    echo '</p>';
                }
                ?>
            </div>
        </div>

        <!-- Database Connection -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <i class="fas fa-database text-green-600"></i>Database Connection
            </h2>
            <div class="space-y-3 text-sm">
                <?php
                try {
                    require_once '../config/db_config.php';
                    
                    if ($conn->connect_error) {
                        echo '<p class="text-red-600"><i class="fas fa-times-circle mr-2"></i><strong>Error:</strong> ' . htmlspecialchars($conn->connect_error) . '</p>';
                    } else {
                        $db_name = DB_NAME;
                        echo '<p class="text-green-600"><i class="fas fa-check-circle mr-2"></i>Connected to database <code>' . htmlspecialchars($db_name) . '</code></p>';
                        
                        // Check tables
                        echo '<p class="mt-3"><strong>Tables:</strong></p>';
                        $tables_to_check = ['admins', 'contact_submissions', 'programs', 'blog_posts'];
                        foreach ($tables_to_check as $table) {
                            $result = $conn->query("SHOW TABLES LIKE '$table'");
                            $exists = $result && $result->num_rows > 0;
                            $status = $exists ? 'text-green-600' : 'text-red-600';
                            $icon = $exists ? 'fa-check-circle' : 'fa-times-circle';
                            echo '<p class="ml-4"><i class="fas ' . $icon . ' ' . $status . ' mr-2"></i>';
                            echo '<code>' . $table . '</code> ';
                            echo $exists ? '<span class="text-green-600">✓ Exists</span>' : '<span class="text-red-600">✗ Missing</span>';
                            echo '</p>';
                        }
                        
                        // Check admin users
                        $admin_result = $conn->query("SELECT COUNT(*) as count FROM admins");
                        if ($admin_result) {
                            $admin_count = $admin_result->fetch_assoc()['count'];
                            echo '<p class="mt-3"><strong>Admin Users:</strong> <code>' . $admin_count . '</code></p>';
                            if ($admin_count == 0) {
                                echo '<p class="text-orange-600 text-xs mt-1">⚠ No admin users found. Use the Setup Tool to create one.</p>';
                            }
                        }
                        
                        $conn->close();
                    }
                } catch (Exception $e) {
                    echo '<p class="text-red-600"><i class="fas fa-times-circle mr-2"></i><strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
                }
                ?>
            </div>
        </div>

        <!-- Next Steps -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold text-blue-900 mb-4">
                <i class="fas fa-lightbulb text-blue-600 mr-2"></i>Next Steps
            </h2>
            <ol class="space-y-2 text-sm text-blue-900 list-decimal list-inside">
                <li>Check the diagnostic information above</li>
                <li>If tables are missing, <a href="../admin_setup.php" class="text-blue-600 font-semibold hover:underline">run the Setup Tool</a></li>
                <li>If admin users are missing, create one in the Setup Tool</li>
                <li>Then try to <a href="index.php" class="text-blue-600 font-semibold hover:underline">access the admin panel</a></li>
            </ol>
        </div>

        <!-- Help Links -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="index.php" class="bg-white p-4 rounded-lg shadow-md hover:shadow-lg transition text-center">
                <i class="fas fa-lock text-2xl text-blue-600 mb-2"></i>
                <h3 class="font-semibold text-gray-900">Admin Login</h3>
                <p class="text-xs text-gray-600 mt-1">Try logging in</p>
            </a>
            <a href="../admin_setup.php" class="bg-white p-4 rounded-lg shadow-md hover:shadow-lg transition text-center">
                <i class="fas fa-tools text-2xl text-green-600 mb-2"></i>
                <h3 class="font-semibold text-gray-900">Setup Tool</h3>
                <p class="text-xs text-gray-600 mt-1">Configure database</p>
            </a>
            <a href="/" class="bg-white p-4 rounded-lg shadow-md hover:shadow-lg transition text-center">
                <i class="fas fa-home text-2xl text-amber-600 mb-2"></i>
                <h3 class="font-semibold text-gray-900">Back Home</h3>
                <p class="text-xs text-gray-600 mt-1">Return to website</p>
            </a>
        </div>
    </div>

</body>
</html>
