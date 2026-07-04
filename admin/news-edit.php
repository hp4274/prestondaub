<?php
$query = $_SERVER['QUERY_STRING'] ? "?" . $_SERVER['QUERY_STRING'] : "";
header("Location: news-editor.html" . $query);
exit();
