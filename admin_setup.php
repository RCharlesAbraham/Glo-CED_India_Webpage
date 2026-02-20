<?php
/**
 * Admin Password Reset/Setup Tool
 * Use this to reset admin password and verify database setup
 */

// Include database config
require_once 'config/db_config.php';

// Check if form was submitted
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'reset_password') {
        $username = trim($_POST['username'] ?? '');
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validation
        if (empty($username) || empty($new_password)) {
            $error = 'Username and password are required.';
        } else if ($new_password !== $confirm_password) {
            $error = 'Passwords do not match.';
        } else if (strlen($new_password) < 6) {
            $error = 'Password must be at least 6 characters long.';
        } else {
            // Hash the password
            $password_hash = password_hash($new_password, PASSWORD_BCRYPT, ['cost' => 10]);
            
            // Update password
            $query = "UPDATE admins SET password_hash = ? WHERE username = ?";
            $stmt = $conn->prepare($query);
            if ($stmt) {
                $stmt->bind_param('ss', $password_hash, $username);
                if ($stmt->execute()) {
                    if ($stmt->affected_rows > 0) {
                        $message = "✓ Password for user '<strong>$username</strong>' has been reset successfully!<br/>
                                   <small>You can now login with the new password.</small>";
                    } else {
                        $error = "User '<strong>$username</strong>' not found in the database.";
                    }
                } else {
                    $error = 'Database error: ' . $stmt->error;
                }
                $stmt->close();
            }
        }
    } elseif ($action === 'create_admin') {
        $username = trim($_POST['new_admin_username'] ?? '');
        $email = trim($_POST['new_admin_email'] ?? '');
        $password = $_POST['new_admin_password'] ?? '';
        $confirm_password = $_POST['new_admin_confirm_password'] ?? '';
        
        // Validation
        if (empty($username) || empty($password)) {
            $error = 'Username and password are required.';
        } else if ($password !== $confirm_password) {
            $error = 'Passwords do not match.';
        } else if (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters long.';
        } else {
            // Hash the password
            $password_hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
            
            // Create admin
            $query = "INSERT INTO admins (username, email, password_hash, is_active) VALUES (?, ?, ?, 1)";
            $stmt = $conn->prepare($query);
            if ($stmt) {
                $stmt->bind_param('sss', $username, $email, $password_hash);
                if ($stmt->execute()) {
                    $message = "✓ New admin user '<strong>$username</strong>' created successfully!<br/>
                               <small>You can now login with this account.</small>";
                } else {
                    $error = 'Error creating admin: ' . $stmt->error;
                }
                $stmt->close();
            }
        }
    }
}

// Get admin users
$admins = array();
$query = "SELECT id, username, email, is_active, created_at FROM admins ORDER BY created_at DESC";
$result = $conn->query($query);
if ($result) {
    $admins = $result->fetch_all(MYSQLI_ASSOC);
}

// Check database status
$db_status = 'Connected';
$admins_table_exists = false;
$tables_check = $conn->query("SHOW TABLES LIKE 'admins'");
if ($tables_check && $tables_check->num_rows > 0) {
    $admins_table_exists = true;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Setup & Password Reset | Glo-CED India</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen py-12 px-4">

    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-12">
            <div class="w-16 h-16 bg-blue-600 text-white rounded-2xl flex items-center justify-center text-3xl mx-auto mb-4">
                <i class="fas fa-tools"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-900">Admin Setup Tool</h1>
            <p class="text-gray-600 mt-2">Reset passwords or create new admin accounts</p>
        </div>

        <!-- Database Status -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-8 border-l-4 <?php echo $admins_table_exists ? 'border-green-500' : 'border-red-500'; ?>">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Database Status</h3>
                    <p class="text-sm text-gray-600 mt-1">
                        <strong>Connection:</strong> <span class="text-green-600">✓ Connected</span><br/>
                        <strong>Database:</strong> <span class="<?php echo $admins_table_exists ? 'text-green-600' : 'text-red-600'; ?>">
                            <?php echo $admins_table_exists ? '✓ Admins table exists' : '✗ Admins table not found'; ?>
                        </span>
                    </p>
                </div>
                <div class="text-4xl <?php echo $admins_table_exists ? 'text-green-500' : 'text-red-500'; ?>">
                    <i class="fas <?php echo $admins_table_exists ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                </div>
            </div>
        </div>

        <!-- Error/Success Messages -->
        <?php if ($error): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl p-4 mb-6 flex items-start gap-3">
            <i class="fas fa-exclamation-circle text-lg mt-0.5"></i>
            <span><?php echo $error; ?></span>
        </div>
        <?php endif; ?>

        <?php if ($message): ?>
        <div class="bg-green-50 border border-green-200 text-green-700 rounded-xl p-4 mb-6 flex items-start gap-3">
            <i class="fas fa-check-circle text-lg mt-0.5"></i>
            <span><?php echo $message; ?></span>
        </div>
        <?php endif; ?>

        <!-- Main Content -->
        <?php if ($admins_table_exists): ?>

            <!-- Existing Admins -->
            <?php if (count($admins) > 0): ?>
            <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">
                    <i class="fas fa-users text-blue-600 mr-2"></i>Existing Admin Accounts
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="text-left px-4 py-2 font-semibold text-gray-700">Username</th>
                                <th class="text-left px-4 py-2 font-semibold text-gray-700">Email</th>
                                <th class="text-left px-4 py-2 font-semibold text-gray-700">Status</th>
                                <th class="text-left px-4 py-2 font-semibold text-gray-700">Created</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($admins as $admin): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-900"><?php echo htmlspecialchars($admin['username']); ?></td>
                                <td class="px-4 py-3 text-gray-600"><?php echo htmlspecialchars($admin['email'] ?? 'N/A'); ?></td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium <?php echo $admin['is_active'] ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>">
                                        <?php echo $admin['is_active'] ? '✓ Active' : '✗ Inactive'; ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-600 text-xs"><?php echo date('M d, Y', strtotime($admin['created_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- Reset Password Form -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-8 border-t-4 border-amber-500">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">
                    <i class="fas fa-key text-amber-600 mr-2"></i>Reset Admin Password
                </h3>
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="reset_password">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                        <input type="text" name="username" required
                            placeholder="Enter admin username" 
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                        <input type="password" name="new_password" required
                            placeholder="Enter new password (min 6 chars)"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                        <input type="password" name="confirm_password" required
                            placeholder="Confirm new password"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition">
                    </div>

                    <button type="submit" class="w-full bg-amber-600 hover:bg-amber-700 text-white font-semibold py-2.5 rounded-lg transition">
                        <i class="fas fa-sync-alt mr-2"></i>Reset Password
                    </button>
                </form>
            </div>

            <!-- Create New Admin Form -->
            <div class="bg-white rounded-xl shadow-sm p-6 border-t-4 border-green-500">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">
                    <i class="fas fa-user-plus text-green-600 mr-2"></i>Create New Admin User
                </h3>
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="create_admin">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                        <input type="text" name="new_admin_username" required
                            placeholder="Enter new username"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email (Optional)</label>
                        <input type="email" name="new_admin_email"
                            placeholder="Enter email address"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input type="password" name="new_admin_password" required
                            placeholder="Enter password (min 6 chars)"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                        <input type="password" name="new_admin_confirm_password" required
                            placeholder="Confirm password"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition">
                    </div>

                    <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2.5 rounded-lg transition">
                        <i class="fas fa-plus mr-2"></i>Create New Admin
                    </button>
                </form>
            </div>

        <?php else: ?>

            <!-- Database Setup Required -->
            <div class="bg-red-50 border border-red-200 rounded-xl p-8 text-center">
                <div class="text-5xl text-red-500 mb-4">
                    <i class="fas fa-database"></i>
                </div>
                <h3 class="text-xl font-semibold text-red-900 mb-2">Database Setup Required</h3>
                <p class="text-red-700 mb-6">The admins table doesn't exist in your database. Please run the database setup first.</p>
                
                <div class="bg-white rounded-lg p-6 text-left mb-6">
                    <h4 class="font-semibold text-gray-900 mb-3">Setup Instructions:</h4>
                    <ol class="space-y-2 text-gray-700 text-sm">
                        <li><strong>1.</strong> Open phpMyAdmin (http://localhost/phpmyadmin/)</li>
                        <li><strong>2.</strong> Go to the "Import" tab</li>
                        <li><strong>3.</strong> Select file: <code class="bg-gray-100 px-2 py-1 rounded">config/database_setup.sql</code></li>
                        <li><strong>4.</strong> Click "Import" button</li>
                        <li><strong>5.</strong> Refresh this page</li>
                    </ol>
                </div>

                <a href="javascript:location.reload()" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2.5 rounded-lg transition">
                    <i class="fas fa-redo mr-2"></i>Refresh Page
                </a>
            </div>

        <?php endif; ?>
    </div>

    <!-- Footer -->
    <div class="text-center mt-12 text-gray-600 text-sm">
        <p>&copy; 2026 Glo-CED India. Admin Setup Tool.</p>
        <a href="admin/index.php" class="text-blue-600 hover:text-blue-700 font-medium">Back to Admin Login</a>
    </div>

</body>
</html>

<?php
$conn->close();
?>
