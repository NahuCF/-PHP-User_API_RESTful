<?php

error_reporting(0);

require "config.php";
require "classes/user.class.php";

$user = new User($db_config);
$user->API();

?>