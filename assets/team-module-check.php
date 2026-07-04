<?php
header("Content-Type: application/json");
http_response_code(410);
echo json_encode([
    "success" => false,
    "message" => "Legacy team module PHP check retired. Use /api/public/team-members instead."
]);
exit();
