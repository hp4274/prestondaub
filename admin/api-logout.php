<?php
require_once __DIR__ . '/config/auth.php';
header('Content-Type: application/json');

logout();
echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
