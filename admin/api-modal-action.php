<?php
$id = $_GET['id'] ?? '';
$path = $id ? "/api/admin/forms/" . rawurlencode($id) . "/actions" : "/api/admin/forms/actions";
header("Location: " . $path, true, 307);
exit();
