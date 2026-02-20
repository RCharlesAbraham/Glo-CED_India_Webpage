<?php
/**
 * Admin Panel - Main Dashboard
 * Glo-CED India
 * 
 * This is the main entry point for the admin panel.
 * Provides authentication and dashboard access to admin functions.
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
        header('Location: admin_submissions.php');
        exit;
    } else {
        $login_error = 'Invalid username or password. Please try again.';
    }
}

// Check logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// If logged in, show dashboard
if (isset($_SESSION['admin_logged_in'])) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Dashboard | Glo-CED India</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
            body { font-family: 'Inter', sans-serif; }
            .card-hover { transition: transform 0.3s ease, box-shadow 0.3s ease; }
            .card-hover:hover { transform: translateY(-4px); box-shadow: 0 12px 40px rgba(0,0,0,0.15); }
        </style>
    </head>
    <body class="bg-gray-100">

        <!-- Navigation Bar -->
        <nav class="bg-blue-900 text-white shadow-lg">
            <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <i class="fas fa-lock-open text-2xl"></i>
                    <h1 class="text-2xl font-bold">Admin Panel</h1>
                </div>
                <div class="flex items-center gap-6">
                    <span class="text-blue-100">Welcome, <strong><?php echo htmlspecialchars($_SESSION['admin_user']); ?></strong></span>
                    <a href="index.php?logout=1" class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded-lg font-semibold transition">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </a>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-6 py-12">
            <!-- Header -->
            <div class="mb-12">
                <h2 class="text-4xl font-bold text-gray-900 mb-3">Dashboard</h2>
                <p class="text-gray-600">Manage your admin panel and organization data</p>
            </div>

            <!-- Dashboard Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">

                <!-- Card: Contact Submissions -->
                <div class="bg-white rounded-xl p-8 shadow-md card-hover border-l-4 border-blue-600">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">Contact Submissions</h3>
                            <p class="text-gray-600 text-sm">View and manage contact form submissions</p>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center text-xl">
                            <i class="fas fa-envelope"></i>
                        </div>
                    </div>
                    <a href="admin_submissions.php" class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold transition">
                        <i class="fas fa-arrow-right mr-2"></i>View Submissions
                    </a>
                </div>

                <!-- Card: Manage Admin Users -->
                <div class="bg-white rounded-xl p-8 shadow-md card-hover border-l-4 border-green-600">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">Admin Users</h3>
                            <p class="text-gray-600 text-sm">Create, edit, and manage admin accounts</p>
                        </div>
                        <div class="w-12 h-12 bg-green-100 text-green-600 rounded-lg flex items-center justify-center text-xl">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <a href="admin_manage_users.php" class="inline-block bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-semibold transition">
                        <i class="fas fa-arrow-right mr-2"></i>Manage Users
                    </a>
                </div>

                <!-- Card: System Info -->
                <div class="bg-white rounded-xl p-8 shadow-md card-hover border-l-4 border-amber-600">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">System Status</h3>
                            <p class="text-gray-600 text-sm">Check system information and status</p>
                        </div>
                        <div class="w-12 h-12 bg-amber-100 text-amber-600 rounded-lg flex items-center justify-center text-xl">
                            <i class="fas fa-info-circle"></i>
                        </div>
                    </div>
                    <div class="space-y-2 text-sm mb-4">
                        <p><span class="font-semibold text-gray-700">PHP Version:</span> <span class="text-gray-600"><?php echo phpversion(); ?></span></p>
                        <p><span class="font-semibold text-gray-700">Server:</span> <span class="text-gray-600"><?php echo $_SERVER['SERVER_SOFTWARE']; ?></span></p>
                        <?php
                        // Check database connection
                        $db_status = $conn->ping() ? '<span class="text-green-600"><i class="fas fa-check"></i> Connected</span>' : '<span class="text-red-600"><i class="fas fa-times"></i> Disconnected</span>';
                        echo '<p><span class="font-semibold text-gray-700">Database:</span> ' . $db_status . '</p>';
                        ?>
                    </div>
                </div>

            </div>

            <!-- Quick Stats Section -->
            <div class="mt-16">
                <h3 class="text-2xl font-bold text-gray-900 mb-8">Quick Stats</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

                    <!-- Total Submissions -->
                    <div class="bg-gradient-to-br from-blue-600 to-blue-700 text-white rounded-xl p-8 shadow-md">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-blue-100 text-sm font-semibold mb-2">Total Submissions</p>
                                <p class="text-4xl font-bold">
                                    <?php
                                    $query = "SELECT COUNT(*) as count FROM contact_submissions";
                                    $result = $conn->query($query);
                                    $row = $result->fetch_assoc();
                                    echo $row['count'] ?? 0;
                                    ?>
                                </p>
                            </div>
                            <i class="fas fa-envelope text-5xl opacity-20"></i>
                        </div>
                    </div>

                    <!-- Admin Users Count -->
                    <div class="bg-gradient-to-br from-green-600 to-green-700 text-white rounded-xl p-8 shadow-md">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-green-100 text-sm font-semibold mb-2">Admin Users</p>
                                <p class="text-4xl font-bold">
                                    <?php
                                    $query = "SELECT COUNT(*) as count FROM admins WHERE is_active = 1";
                                    $result = $conn->query($query);
                                    $row = $result->fetch_assoc();
                                    echo $row['count'] ?? 0;
                                    ?>
                                </p>
                            </div>
                            <i class="fas fa-users text-5xl opacity-20"></i>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Help Section -->
            <div class="mt-16 bg-blue-50 border border-blue-200 rounded-xl p-8">
                <h3 class="text-xl font-bold text-gray-900 mb-4">
                    <i class="fas fa-lightbulb text-blue-600 mr-2"></i>Need Help?
                </h3>
                <p class="text-gray-700 mb-4">
                    This admin panel allows you to manage contact submissions and admin user accounts. Use the menu items above to access different administration functions.
                </p>
                <ul class="list-none space-y-2 text-gray-700">
                    <li class="flex items-center gap-2">
                        <i class="fas fa-check text-green-600"></i>
                        <strong>Contact Submissions:</strong> View and respond to form submissions from your website visitors
                    </li>
                    <li class="flex items-center gap-2">
                        <i class="fas fa-check text-green-600"></i>
                        <strong>Admin Users:</strong> Create new admin accounts and manage existing ones
                    </li>
                    <li class="flex items-center gap-2">
                        <i class="fas fa-check text-green-600"></i>
                        <strong>System Status:</strong> Monitor database connection and server information
                    </li>
                </ul>
            </div>

        </div>

        <!-- Footer -->
        <footer class="bg-gray-900 text-gray-400 mt-16 py-8 border-t">
            <div class="max-w-7xl mx-auto px-6 flex justify-between items-center">
                <p>&copy; 2026 Glo-CED India. All rights reserved.</p>
                <a href="index.php?logout=1" class="text-gray-300 hover:text-white">Logout</a>
            </div>
        </footer>

    </body>
    </html>

    <?php
} else {
    // Show login form
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
            @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
            body { 
                font-family: 'Inter', sans-serif;
                background: linear-gradient(135deg, #1e3a5f 0%, #1e40af 50%, #1d4ed8 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }
        </style>
    </head>
    <body>

        <div class="w-full max-w-md">
            <div class="bg-white rounded-2xl shadow-2xl p-8">
                <!-- Logo Section -->
                <div class="text-center mb-8">
                    <div class="w-16 h-16 bg-blue-600 text-white rounded-2xl flex items-center justify-center text-3xl mx-auto mb-4">
                        <i class="fas fa-lock"></i>
                    </div>
                    <h1 class="text-3xl font-bold text-gray-900">Admin Panel</h1>
                    <p class="text-gray-600 mt-2">Glo-CED India</p>
                </div>

                <!-- Login Form -->
                <form method="POST" class="space-y-6">
                    <!-- Error Message -->
                    <?php if (isset($login_error)): ?>
                    <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-4 flex items-start gap-3">
                        <i class="fas fa-exclamation-circle mt-1"></i>
                        <span><?php echo htmlspecialchars($login_error); ?></span>
                    </div>
                    <?php endif; ?>

                    <!-- Username Field -->
                    <div>
                        <label for="admin_user" class="block text-sm font-semibold text-gray-900 mb-2">
                            <i class="fas fa-user mr-2"></i>Username
                        </label>
                        <input 
                            type="text" 
                            id="admin_user" 
                            name="admin_user" 
                            placeholder="Enter your username" 
                            required 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition"
                            autocomplete="username"
                        >
                    </div>

                    <!-- Password Field -->
                    <div>
                        <label for="admin_password" class="block text-sm font-semibold text-gray-900 mb-2">
                            <i class="fas fa-lock mr-2"></i>Password
                        </label>
                        <input 
                            type="password" 
                            id="admin_password" 
                            name="admin_password" 
                            placeholder="Enter your password" 
                            required 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition"
                            autocomplete="current-password"
                        >
                    </div>

                    <!-- Submit Button -->
                    <button 
                        type="submit" 
                        name="login" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg transition shadow-md hover:shadow-lg flex items-center justify-center gap-2"
                    >
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                </form>

                <!-- Info Message -->
                <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-4 text-sm text-gray-700">
                    <p class="flex items-start gap-2">
                        <i class="fas fa-info-circle text-blue-600 mt-0.5"></i>
                        <span>Use your admin credentials to access the admin panel. If you don't have credentials, contact your system administrator.</span>
                    </p>
                </div>

            </div>

            <!-- Footer -->
            <div class="text-center mt-8 text-white text-sm">
                <p>&copy; 2026 Glo-CED India. All rights reserved.</p>
            </div>
        </div>

    </body>
    </html>

    <?php
}
?>
