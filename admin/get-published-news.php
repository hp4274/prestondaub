<?php
$query = $_SERVER['QUERY_STRING'] ? "?" . $_SERVER['QUERY_STRING'] : "";
header("Location: /api/public/news" . $query, true, 307);
exit();
