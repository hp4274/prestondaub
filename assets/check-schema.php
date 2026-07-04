<?php
header("Content-Type: application/json");
http_response_code(410);
echo json_encode([
    "success" => false,
    "message" => "This legacy PHP schema check was removed. Use Supabase SQL editor and server/supabase/schema.sql instead."
]);
exit();
