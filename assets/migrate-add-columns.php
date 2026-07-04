<?php
header("Content-Type: application/json");
http_response_code(410);
echo json_encode([
    "success" => false,
    "message" => "This legacy PHP migration helper was removed. Apply schema changes via Supabase migrations instead."
]);
exit();
