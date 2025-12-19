<?php
/**
 * Admin Panel - Logout
 */

require_once '../includes/config.php';

logout();
setFlash('success', 'You have been logged out successfully.');
redirect(SITE_URL . '/admin/');
