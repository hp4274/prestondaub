<?php
header("Content-Type: application/json");
http_response_code(410);
echo json_encode([
    "success" => false,
    "message" => "Legacy PHP modal view retired. Use /api/admin/forms/{id}/detail from the Node backend."
]);
exit();
