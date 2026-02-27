<?php
/**
 * Admin User Management UI
 * Requires authentication to access
 * Allows adding, editing, and deleting admin user accounts
 */

session_start();
require_once '../config/db_config.php';
require_once 'admin_users.php';

// Check authentication - redirect to login if not authenticated
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

$message = '';
$error = '';

// Handle add new admin
if (isset($_POST['add_admin'])) {
    $username = trim($_POST['new_username'] ?? '');
    $password = $_POST['new_password'] ?? '';
    $email = trim($_POST['new_email'] ?? '');
    
    if (empty($username) || empty($password)) {
        $error = 'Username and password are required.';
    } else if (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $query = "INSERT INTO admins (username, password_hash, email, is_active) VALUES (?, ?, ?, 1)";
        $stmt = $conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param('sss', $username, $password_hash, $email);
            if ($stmt->execute()) {
                $message = 'Admin user added successfully.';
            } else {
                $error = 'Error adding user: ' . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Handle delete admin
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    // Prevent deleting current user
    $current_user_query = "SELECT id FROM admins WHERE username = ?";
    $stmt = $conn->prepare($current_user_query);
    $stmt->bind_param('s', $_SESSION['admin_user']);
    $stmt->execute();
    $result = $stmt->get_result();
    $current_user = $result->fetch_assoc();
    $stmt->close();
    
    if ($current_user && $current_user['id'] == $delete_id) {
        $error = 'You cannot delete your own account.';
    } else {
        $query = "DELETE FROM admins WHERE id = ?";
        $stmt = $conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param('i', $delete_id);
            if ($stmt->execute()) {
                $message = 'Admin user deleted successfully.';
            } else {
                $error = 'Error deleting user.';
            }
            $stmt->close();
        }
    }
}

// Handle edit admin
if (isset($_POST['edit_admin'])) {
    $admin_id = (int)$_POST['admin_id'];
    $username = trim($_POST['edit_username'] ?? '');
    $email = trim($_POST['edit_email'] ?? '');
    $new_password = $_POST['edit_password'] ?? '';
    
    if (empty($username)) {
        $error = 'Username is required.';
    } else {
        if (!empty($new_password)) {
            if (strlen($new_password) < 6) {
                $error = 'Password must be at least 6 characters.';
            } else {
                $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                $query = "UPDATE admins SET username = ?, password_hash = ?, email = ? WHERE id = ?";
                $stmt = $conn->prepare($query);
                if ($stmt) {
                    $stmt->bind_param('sssi', $username, $password_hash, $email, $admin_id);
                    if ($stmt->execute()) {
                        $message = 'Admin user updated successfully.';
                    } else {
                        $error = 'Error updating user: ' . $stmt->error;
                    }
                    $stmt->close();
                }
            }
        } else {
            $query = "UPDATE admins SET username = ?, email = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            if ($stmt) {
                $stmt->bind_param('ssi', $username, $email, $admin_id);
                if ($stmt->execute()) {
                    $message = 'Admin user updated successfully.';
                } else {
                    $error = 'Error updating user: ' . $stmt->error;
                }
                $stmt->close();
            }
        }
    }
}

// Get all admin users
$query = "SELECT id, username, email, is_active, created_at FROM admins ORDER BY created_at DESC";
$result = $conn->query($query);
$admins = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Admin Users | Glo-CED India</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
        .modal-backdrop { display: none; }
        .modal-backdrop.show { display: flex; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">

    <!-- Admin Navbar -->
    <nav class="bg-blue-900 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-4">
                    <a href="index.html" class="text-white font-bold text-xl">Glo-CED India</a>
                    <span class="hidden sm:inline-block text-blue-300 text-sm">| Admin Panel</span>
                </div>
                <div class="flex items-center gap-3">
                    <a href="admin_submissions.php" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-800 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
                        <i class="fas fa-arrow-left"></i> <span class="hidden sm:inline">Submissions</span>
                    </a>
                    <a href="index.php" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-800 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
                        <i class="fas fa-home"></i> <span class="hidden sm:inline">Dashboard</span>
                    </a>
                    <a href="index.php?logout=1" class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition">
                        <i class="fas fa-sign-out-alt"></i> <span class="hidden sm:inline">Logout</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Manage Admin Users</h1>
            <p class="text-gray-500 mt-1">Add, edit, or remove admin accounts</p>
        </div>

        <!-- Alerts -->
        <?php if ($message): ?>
            <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl flex items-center gap-3">
                <i class="fas fa-check-circle text-green-500 text-lg"></i>
                <span><?php echo htmlspecialchars($message); ?></span>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl flex items-center gap-3">
                <i class="fas fa-exclamation-circle text-red-500 text-lg"></i>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <!-- Add New Admin Card -->
        <div class="bg-white rounded-2xl shadow-md p-6 sm:p-8 mb-8">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-user-plus text-blue-600"></i>
                </div>
                <h2 class="text-xl font-semibold text-gray-900">Add New Admin User</h2>
            </div>
            <form method="POST" class="space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="new_username" class="block text-sm font-medium text-gray-700 mb-1">Username <span class="text-red-500">*</span></label>
                        <input type="text" id="new_username" name="new_username" required
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                            placeholder="Enter username">
                    </div>
                    <div>
                        <label for="new_email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" id="new_email" name="new_email"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                            placeholder="Enter email address">
                    </div>
                </div>
                <div>
                    <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">Password <span class="text-red-500">*</span></label>
                    <input type="password" id="new_password" name="new_password" required minlength="6"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                        placeholder="Minimum 6 characters">
                </div>
                <button type="submit" name="add_admin"
                    class="inline-flex items-center gap-2 px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl shadow-sm hover:shadow transition">
                    <i class="fas fa-plus"></i> Add Admin
                </button>
            </form>
        </div>

        <!-- Admin Users Table Card -->
        <div class="bg-white rounded-2xl shadow-md p-6 sm:p-8">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-users text-green-600"></i>
                    </div>
                    <h2 class="text-xl font-semibold text-gray-900">Admin Users</h2>
                </div>
                <span class="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-700 text-sm font-medium rounded-full">
                    <?php echo count($admins); ?> total
                </span>
            </div>

            <?php if (count($admins) > 0): ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Username</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">Email</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">Status</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden lg:table-cell">Created</th>
                            <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($admins as $admin): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white text-sm font-bold">
                                        <?php echo strtoupper(substr($admin['username'], 0, 1)); ?>
                                    </div>
                                    <span class="font-medium text-gray-900"><?php echo htmlspecialchars($admin['username']); ?></span>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-gray-600 hidden sm:table-cell"><?php echo htmlspecialchars($admin['email'] ?? '—'); ?></td>
                            <td class="px-4 py-4 hidden md:table-cell">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-green-100 text-green-700 text-xs font-medium rounded-full">
                                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span> Active
                                </span>
                            </td>
                            <td class="px-4 py-4 text-gray-500 text-sm hidden lg:table-cell"><?php echo date('M d, Y', strtotime($admin['created_at'])); ?></td>
                            <td class="px-4 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button onclick="openEditModal(<?php echo $admin['id']; ?>, '<?php echo htmlspecialchars($admin['username']); ?>', '<?php echo htmlspecialchars($admin['email'] ?? ''); ?>')"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 text-sm font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition">
                                        <i class="fas fa-edit text-xs"></i> Edit
                                    </button>
                                    <?php if ($admin['username'] !== ($_SESSION['admin_user'] ?? '')): ?>
                                    <a href="?delete_id=<?php echo $admin['id']; ?>" onclick="return confirm('Are you sure you want to delete this admin user?')"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 text-sm font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition">
                                        <i class="fas fa-trash text-xs"></i> Delete
                                    </a>
                                    <?php else: ?>
                                    <span class="text-xs text-gray-400 italic">Current user</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="text-center py-12">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-users text-gray-400 text-2xl"></i>
                </div>
                <p class="text-gray-500">No admin users found.</p>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Edit Modal -->
    <div id="editModal" class="modal-backdrop fixed inset-0 z-50 items-center justify-center bg-black/50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-auto p-6 sm:p-8 relative">
            <button onclick="closeEditModal()" class="absolute top-4 right-4 w-8 h-8 flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition">
                <i class="fas fa-times"></i>
            </button>
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-user-edit text-blue-600"></i>
                </div>
                <h2 class="text-xl font-semibold text-gray-900">Edit Admin User</h2>
            </div>
            <form method="POST" class="space-y-5">
                <input type="hidden" id="edit_admin_id" name="admin_id">
                <div>
                    <label for="edit_username" class="block text-sm font-medium text-gray-700 mb-1">Username <span class="text-red-500">*</span></label>
                    <input type="text" id="edit_username" name="edit_username" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                </div>
                <div>
                    <label for="edit_email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" id="edit_email" name="edit_email"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                </div>
                <div>
                    <label for="edit_password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                    <input type="password" id="edit_password" name="edit_password"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                        placeholder="Leave blank to keep current">
                </div>
                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" name="edit_admin"
                        class="inline-flex items-center gap-2 px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl shadow-sm hover:shadow transition">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                    <button type="button" onclick="closeEditModal()"
                        class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-xl transition">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(id, username, email) {
            document.getElementById('edit_admin_id').value = id;
            document.getElementById('edit_username').value = username;
            document.getElementById('edit_email').value = email;
            document.getElementById('editModal').classList.add('show');
        }
        function closeEditModal() {
            document.getElementById('editModal').classList.remove('show');
        }
        window.addEventListener('click', function(e) {
            var modal = document.getElementById('editModal');
            if (e.target === modal) closeEditModal();
        });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeEditModal();
        });
    </script>
</body>
</html>