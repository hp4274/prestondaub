<?php
$query = $_SERVER['QUERY_STRING'] ? "?" . $_SERVER['QUERY_STRING'] : "";
header("Location: /api/admin/forms/poll" . $query, true, 307);
exit();
