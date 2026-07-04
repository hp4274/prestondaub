<?php
header("Content-Type: application/json");
http_response_code(410);
echo json_encode([
    "success" => false,
    "message" => "This legacy PHP diagnostic script was removed. Use the Node/Supabase backend and server logs instead."
]);
exit();
