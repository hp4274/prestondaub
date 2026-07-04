<?php
header("Content-Type: application/json");
http_response_code(410);
echo json_encode([
    "success" => false,
    "message" => "Legacy sports category stats endpoint retired. Use the Node admin forms APIs instead."
]);
exit();
