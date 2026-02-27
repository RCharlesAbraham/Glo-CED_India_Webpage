<?php
/**
 * Admin - Get Single Submission Details
 * AJAX endpoint for loading submission details in modal
 */

session_start();

// Check authentication
if (!isset($_SESSION['admin_logged_in'])) {
    echo 'Unauthorized access';
    exit;
}

require_once __DIR__ . '/../../../config/db_config.php';

if (!isset($_GET['id'])) {
    echo 'Invalid submission ID';
    exit;
}

$submission_id = (int)$_GET['id'];

$query = "SELECT * FROM contact_submissions WHERE id = ?";
$stmt = prepare_query($conn, $query, array($submission_id));
$stmt->execute();
$result = $stmt->get_result();
$submission = $result->fetch_assoc();
$stmt->close();

if (!$submission) {
    echo 'Submission not found';
    exit;
}

?>

<form method="POST" action="admin_submissions.php">

?>

<form method="POST" action="admin_submissions.php" class="space-y-5">
    <input type="hidden" name="submission_id" value="<?php echo $submission['id']; ?>">
    
    <!-- Contact Info Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Name</label>
            <p class="px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-gray-900 font-medium"><?php echo htmlspecialchars($submission['name']); ?></p>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Email</label>
            <p class="px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-gray-900">
                <a href="mailto:<?php echo htmlspecialchars($submission['email']); ?>" class="text-blue-600 hover:text-blue-700"><?php echo htmlspecialchars($submission['email']); ?></a>
            </p>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Phone</label>
            <p class="px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-gray-900"><?php echo htmlspecialchars($submission['phone']); ?></p>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Submitted At</label>
            <p class="px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-gray-900 text-sm"><?php echo date('M d, Y \a\t h:i A', strtotime($submission['submitted_at'])); ?></p>
        </div>
    </div>

    <!-- Message -->
    <div>
        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Message</label>
        <div class="px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-gray-700 leading-relaxed text-sm min-h-[80px]"><?php echo nl2br(htmlspecialchars($submission['message'])); ?></div>
    </div>

    <!-- IP Address -->
    <div>
        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">IP Address</label>
        <p class="px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-gray-500 text-sm font-mono"><?php echo htmlspecialchars($submission['ip_address']); ?></p>
    </div>

    <hr class="border-gray-200">

    <!-- Status -->
    <div>
        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
        <select id="status" name="status" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition text-sm">
            <option value="new" <?php echo ($submission['status'] === 'new') ? 'selected' : ''; ?>>New</option>
            <option value="read" <?php echo ($submission['status'] === 'read') ? 'selected' : ''; ?>>Read</option>
            <option value="replied" <?php echo ($submission['status'] === 'replied') ? 'selected' : ''; ?>>Replied</option>
            <option value="archived" <?php echo ($submission['status'] === 'archived') ? 'selected' : ''; ?>>Archived</option>
        </select>
    </div>

    <!-- Admin Notes -->
    <div>
        <label for="admin_notes" class="block text-sm font-medium text-gray-700 mb-1">Admin Notes</label>
        <textarea id="admin_notes" name="admin_notes" rows="3" placeholder="Add internal notes about this submission..."
            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition resize-none text-sm"><?php echo htmlspecialchars($submission['admin_notes'] ?? ''); ?></textarea>
    </div>

    <!-- Buttons -->
    <div class="flex items-center gap-3 pt-2">
        <button type="submit" name="update_status"
            class="inline-flex items-center gap-2 px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl shadow-sm hover:shadow transition text-sm">
            <i class="fas fa-save"></i> Save Changes
        </button>
        <button type="button" onclick="closeModal()"
            class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-xl transition text-sm">
            Close
        </button>
    </div>
</form>

<?php
$conn->close();
?>