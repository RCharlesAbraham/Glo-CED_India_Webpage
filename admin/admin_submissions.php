<?php
/**
 * Admin Panel - Contact Submissions Management
 * Charity Trust Project
 * 
 * Manages viewing, filtering, and responding to contact form submissions
 */

// Start session
session_start();

// Include database configuration
require_once '../config/db_config.php';
require_once 'admin_users.php';

// Check if user submitted login form
if (isset($_POST['login'])) {
    $entered_user = trim($_POST['admin_user'] ?? '');
    $entered_password = $_POST['admin_password'] ?? '';
    if ($entered_user !== '' && verify_admin($entered_user, $entered_password)) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user'] = $entered_user;
    } else {
        $login_error = 'Invalid username or password. Please try again.';
    }
}

// Check logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin_submissions.php');
    exit;
}

// If not logged in, show login form
// If not logged in, show login form
// If not logged in, show login form
if (!isset($_SESSION['admin_logged_in'])) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Login | Glo-CED India</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
            body { font-family: 'Inter', sans-serif; }
        </style>
    </head>
    <body class="bg-gradient-to-br from-blue-900 via-blue-800 to-blue-900 min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            <!-- Logo / Branding -->
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-white/10 backdrop-blur-sm rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-hands-helping text-white text-2xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-white">Glo-CED India</h1>
                <p class="text-blue-300 text-sm mt-1">Admin Panel</p>
            </div>

            <!-- Login Card -->
            <div class="bg-white rounded-2xl shadow-2xl p-8">
                <h2 class="text-xl font-bold text-gray-900 text-center mb-6">Sign In</h2>

                <?php if (isset($login_error)): ?>
                    <div class="mb-5 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl flex items-center gap-3 text-sm">
                        <i class="fas fa-exclamation-circle text-red-500"></i>
                        <span><?php echo htmlspecialchars($login_error); ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-5">
                    <div>
                        <label for="admin_user" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400"><i class="fas fa-user"></i></span>
                            <input type="text" id="admin_user" name="admin_user" required
                                class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                                placeholder="Enter username">
                        </div>
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400"><i class="fas fa-lock"></i></span>
                            <input type="password" id="password" name="admin_password" required
                                class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                                placeholder="Enter password">
                        </div>
                    </div>
                    <button type="submit" name="login"
                        class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl shadow-md hover:shadow-lg transition flex items-center justify-center gap-2">
                        <i class="fas fa-sign-in-alt"></i> Sign In
                    </button>
                </form>
            </div>

            <p class="text-center text-blue-300/60 text-xs mt-6">&copy; 2025 Glo-CED India. All rights reserved.</p>
        </div>
    </body>
    </html>
    <?php
    exit;
}
 
// Handle delete submission
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    $query = "DELETE FROM contact_submissions WHERE id = ?";
    $stmt = prepare_query($conn, $query, array($delete_id));
    if ($stmt) {
        $stmt->execute();
        $stmt->close();
        $_SESSION['message'] = 'Submission deleted successfully.';
    }
    header('Location: admin_submissions.php');
    exit;
}

// Handle status update
if (isset($_POST['update_status'])) {
    $submission_id = (int)$_POST['submission_id'];
    $new_status = $_POST['status'];
    $admin_notes = $_POST['admin_notes'] ?? '';
    
    $query = "UPDATE contact_submissions SET status = ?, admin_notes = ? WHERE id = ?";
    $stmt = prepare_query($conn, $query, array($new_status, $admin_notes, $submission_id));
    if ($stmt) {
        $stmt->execute();
        $stmt->close();
        $_SESSION['message'] = 'Submission updated successfully.';
    }
    header('Location: admin_submissions.php');
    exit;
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Filter by status
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Get total submissions (with filter if applied)
if ($status_filter) {
    $count_query = "SELECT COUNT(*) as total FROM contact_submissions WHERE status = ?";
    $stmt = prepare_query($conn, $count_query, array($status_filter));
} else {
    $count_query = "SELECT COUNT(*) as total FROM contact_submissions";
    $stmt = $conn->prepare($count_query);
}

$stmt->execute();
$result = $stmt->get_result();
$total_row = $result->fetch_assoc();
$total = $total_row['total'];
$total_pages = ceil($total / $per_page);
$stmt->close();

// Get submissions
if ($status_filter) {
    $query = "SELECT * FROM contact_submissions WHERE status = ? ORDER BY submitted_at DESC LIMIT ? OFFSET ?";
    $stmt = prepare_query($conn, $query, array($status_filter, $per_page, $offset));
} else {
    $query = "SELECT * FROM contact_submissions ORDER BY submitted_at DESC LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ii', $per_page, $offset);
}

$stmt->execute();
$result = $stmt->get_result();
$submissions = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get statistics
$stats_query = "SELECT status, COUNT(*) as count FROM contact_submissions GROUP BY status";
$stats_result = $conn->query($stats_query);
$stats = array('new' => 0, 'read' => 0, 'replied' => 0, 'archived' => 0);
while ($row = $stats_result->fetch_assoc()) {
    $stats[$row['status']] = $row['count'];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Contact Submissions | Glo-CED India</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
        .modal-overlay { display: none; }
        .modal-overlay.show { display: flex; }
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
                <div class="flex items-center gap-2 sm:gap-3">
                    <span class="hidden md:inline text-blue-200 text-sm">Welcome, <?php echo htmlspecialchars($_SESSION['admin_user'] ?? 'Admin'); ?></span>
                    <a href="admin_manage_users.php" class="inline-flex items-center gap-2 px-3 sm:px-4 py-2 bg-blue-800 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
                        <i class="fas fa-users-cog"></i> <span class="hidden sm:inline">Users</span>
                    </a>
                    <a href="?logout=1" class="inline-flex items-center gap-2 px-3 sm:px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition">
                        <i class="fas fa-sign-out-alt"></i> <span class="hidden sm:inline">Logout</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Contact Submissions</h1>
            <p class="text-gray-500 mt-1">Manage and respond to contact form inquiries</p>
        </div>

        <!-- Success Alert -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl flex items-center gap-3">
                <i class="fas fa-check-circle text-green-500 text-lg"></i>
                <span><?php echo htmlspecialchars($_SESSION['message']); ?></span>
                <?php unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-2xl shadow-sm p-5 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1"><?php echo $stats['new'] + $stats['read'] + $stats['replied'] + $stats['archived']; ?></p>
                    </div>
                    <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-inbox text-blue-600"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl shadow-sm p-5 border-l-4 border-amber-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">New</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1"><?php echo $stats['new']; ?></p>
                    </div>
                    <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-star text-amber-600"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl shadow-sm p-5 border-l-4 border-sky-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Read</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1"><?php echo $stats['read']; ?></p>
                    </div>
                    <div class="w-10 h-10 bg-sky-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-envelope-open text-sky-600"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl shadow-sm p-5 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Replied</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1"><?php echo $stats['replied']; ?></p>
                    </div>
                    <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-reply text-green-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="bg-white rounded-2xl shadow-sm p-5 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <h3 class="font-semibold text-gray-700"><i class="fas fa-filter mr-2 text-gray-400"></i>Filter by Status</h3>
                <div class="flex flex-wrap gap-2">
                    <a href="admin_submissions.php" class="px-4 py-2 text-sm font-medium rounded-lg transition <?php echo (!$status_filter) ? 'bg-blue-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'; ?>">All</a>
                    <a href="?status=new" class="px-4 py-2 text-sm font-medium rounded-lg transition <?php echo ($status_filter === 'new') ? 'bg-amber-500 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'; ?>">New</a>
                    <a href="?status=read" class="px-4 py-2 text-sm font-medium rounded-lg transition <?php echo ($status_filter === 'read') ? 'bg-sky-500 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'; ?>">Read</a>
                    <a href="?status=replied" class="px-4 py-2 text-sm font-medium rounded-lg transition <?php echo ($status_filter === 'replied') ? 'bg-green-500 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'; ?>">Replied</a>
                    <a href="?status=archived" class="px-4 py-2 text-sm font-medium rounded-lg transition <?php echo ($status_filter === 'archived') ? 'bg-gray-500 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'; ?>">Archived</a>
                </div>
            </div>
        </div>

        <!-- Submissions Table -->
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">Email</th>
                            <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden lg:table-cell">Phone</th>
                            <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="text-right px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (count($submissions) > 0): ?>
                            <?php foreach ($submissions as $submission): ?>
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-5 py-4 text-sm text-gray-500 whitespace-nowrap"><?php echo date('M d, Y', strtotime($submission['submitted_at'])); ?></td>
                                    <td class="px-5 py-4">
                                        <span class="font-medium text-gray-900"><?php echo htmlspecialchars($submission['name']); ?></span>
                                    </td>
                                    <td class="px-5 py-4 hidden md:table-cell">
                                        <a href="mailto:<?php echo htmlspecialchars($submission['email']); ?>" class="text-blue-600 hover:text-blue-700 text-sm transition"><?php echo htmlspecialchars($submission['email']); ?></a>
                                    </td>
                                    <td class="px-5 py-4 text-sm text-gray-600 hidden lg:table-cell"><?php echo htmlspecialchars($submission['phone']); ?></td>
                                    <td class="px-5 py-4">
                                        <?php
                                        $statusColors = [
                                            'new' => 'bg-amber-100 text-amber-700',
                                            'read' => 'bg-sky-100 text-sky-700',
                                            'replied' => 'bg-green-100 text-green-700',
                                            'archived' => 'bg-gray-100 text-gray-600'
                                        ];
                                        $statusColor = $statusColors[$submission['status']] ?? 'bg-gray-100 text-gray-600';
                                        ?>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium rounded-full <?php echo $statusColor; ?>">
                                            <?php echo ucfirst($submission['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button onclick="openModal(<?php echo $submission['id']; ?>)"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 text-sm font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition">
                                                <i class="fas fa-eye text-xs"></i> View
                                            </button>
                                            <a href="?delete_id=<?php echo $submission['id']; ?>" onclick="return confirm('Are you sure you want to delete this submission?')"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 text-sm font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition">
                                                <i class="fas fa-trash text-xs"></i> Delete
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                            <i class="fas fa-inbox text-gray-400 text-2xl"></i>
                                        </div>
                                        <p class="text-gray-500 font-medium">No submissions found</p>
                                        <p class="text-gray-400 text-sm mt-1">Submissions will appear here when received</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="flex items-center justify-center gap-2 mt-6">
                <?php if ($page > 1): ?>
                    <a href="?page=1<?php echo ($status_filter) ? '&status=' . $status_filter : ''; ?>" class="px-3 py-2 text-sm bg-white border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50 transition">&laquo; First</a>
                    <a href="?page=<?php echo ($page - 1); ?><?php echo ($status_filter) ? '&status=' . $status_filter : ''; ?>" class="px-3 py-2 text-sm bg-white border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50 transition">&laquo; Prev</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="px-3.5 py-2 text-sm bg-blue-600 text-white rounded-lg font-semibold shadow-sm"><?php echo $i; ?></span>
                    <?php elseif ($i <= $page + 2 && $i >= $page - 2): ?>
                        <a href="?page=<?php echo $i; ?><?php echo ($status_filter) ? '&status=' . $status_filter : ''; ?>" class="px-3.5 py-2 text-sm bg-white border border-gray-200 rounded-lg text-gray-700 hover:border-blue-300 hover:text-blue-600 transition"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo ($page + 1); ?><?php echo ($status_filter) ? '&status=' . $status_filter : ''; ?>" class="px-3 py-2 text-sm bg-white border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50 transition">Next &raquo;</a>
                    <a href="?page=<?php echo $total_pages; ?><?php echo ($status_filter) ? '&status=' . $status_filter : ''; ?>" class="px-3 py-2 text-sm bg-white border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50 transition">Last &raquo;</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </main>

    <!-- View/Edit Modal -->
    <div id="submissionModal" class="modal-overlay fixed inset-0 z-50 items-center justify-center bg-black/50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl mx-auto max-h-[90vh] overflow-y-auto relative">
            <div class="sticky top-0 bg-white rounded-t-2xl border-b border-gray-100 px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-blue-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-envelope-open text-blue-600"></i>
                    </div>
                    <h2 class="text-lg font-semibold text-gray-900">Submission Details</h2>
                </div>
                <button onclick="closeModal()" class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-6" id="modalBody">
                <div class="flex items-center justify-center py-12">
                    <i class="fas fa-spinner fa-spin text-blue-500 text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/vendor/jquery-1.12.4.min.js"></script>
    <script>
        function openModal(submissionId) {
            $('#modalBody').html('<div class="flex items-center justify-center py-12"><i class="fas fa-spinner fa-spin text-blue-500 text-2xl"></i></div>');
            $('#submissionModal').addClass('show');
            $.ajax({
                url: 'admin_get_submission.php',
                type: 'GET',
                data: { id: submissionId },
                success: function(response) {
                    $('#modalBody').html(response);
                },
                error: function() {
                    $('#modalBody').html('<div class="text-center py-8 text-red-500"><i class="fas fa-exclamation-circle text-3xl mb-3"></i><p>Error loading submission</p></div>');
                }
            });
        }

        function closeModal() {
            $('#submissionModal').removeClass('show');
        }

        $(document).click(function(event) {
            if (event.target.id === 'submissionModal') {
                closeModal();
            }
        });

        $(document).keydown(function(e) {
            if (e.key === 'Escape') closeModal();
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>