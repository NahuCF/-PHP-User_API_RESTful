<?php

header("Content-Type: aplication/json");
http_response_code(400);
echo json_encode(["status" => 400, "message" => "Bad Request"]);

?>