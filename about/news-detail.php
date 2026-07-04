<?php
$query = $_SERVER['QUERY_STRING'] ? "?" . $_SERVER['QUERY_STRING'] : "";
header("Location: news-detail.html" . $query);
exit();
