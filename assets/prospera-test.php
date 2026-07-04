<?php
header("Content-Type: application/json");
http_response_code(410);
echo json_encode([
    "success" => false,
    "message" => "This legacy PHP test endpoint was removed. Use /api/forms/prospera from the Node backend."
]);
exit();
