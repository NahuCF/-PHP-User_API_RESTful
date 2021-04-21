<?php

require "config.php";
require "classes/user.class.php";

$user = new User($db_config);
$user->API();

?>