<?php
header("Content-Type: application/json");
http_response_code(410);
echo json_encode([
    "success" => false,
    "message" => "This legacy PHP seed script was removed. Create articles via the admin news editor or Supabase directly."
]);
exit();
