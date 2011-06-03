<?php

// Author: Max Jakob (max.jakob@fu-berlin.de)
// Check if username and password are already set.

session_start();

if(isset($_SESSION["username"]) && isset($_SESSION["password"])){
    echo json_encode(array(
        'logged_in' => true,
        'username' => $_SESSION["username"]
    ));
}
else{
    echo json_encode(array(
        'logged_in' => false,
        'message' => 'no user is logged in'
    ));
    die();
}