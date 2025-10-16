<?php
// Reusable change password modal included in layouts
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
?>
<!-- Change Password Modal -->
<div id="changePasswordModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black opacity-50"></div>
    <div class="relative bg-white rounded-lg shadow-lg w-full max-w-md p-6 z-10">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold">Change Password</h3>
            <button type="button" onclick="closeChangePasswordModal()" class="text-gray-500 hover:text-gray-700">&times;</button>
        </div>
        <form id="changePasswordForm" action="../actions/ChangePassword.php" method="POST">
            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700">Current Password</label>
                <input name="current_password" type="password" required class="mt-1 block w-full border border-gray-300 rounded px-3 py-2" />
            </div>
            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700">New Password</label>
                <input name="new_password" id="newPasswordInput" type="password" required class="mt-1 block w-full border border-gray-300 rounded px-3 py-2" />
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                <input name="confirm_password" id="confirmPasswordInput" type="password" required class="mt-1 block w-full border border-gray-300 rounded px-3 py-2" />
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeChangePasswordModal()" class="px-4 py-2 bg-gray-100 rounded">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openChangePasswordModal() {
        const modal = document.getElementById('changePasswordModal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    }

    function closeChangePasswordModal() {
        const modal = document.getElementById('changePasswordModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    // Basic client-side validation
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('changePasswordForm');
        if (!form) return;
        form.addEventListener('submit', function(e) {
            const newP = document.getElementById('newPasswordInput').value;
            const confirmP = document.getElementById('confirmPasswordInput').value;
            if (newP !== confirmP) {
                e.preventDefault();
                alert('New password and confirmation do not match.');
                return false;
            }
            if (newP.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long.');
                return false;
            }
        });
    });
</script>