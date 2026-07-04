<?php
$query = $_SERVER['QUERY_STRING'] ? "?" . $_SERVER['QUERY_STRING'] : "";
header("Location: /api/public/team-members" . $query, true, 307);
exit();
