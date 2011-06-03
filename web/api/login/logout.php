<?php

// Author: Max Jakob (max.jakob@fu-berlin.de)
// Destroy session variables that hold the username and password.

session_start();
$_SESSION = array();

echo json_encode(array(
    'success' => true
));