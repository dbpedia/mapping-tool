<?php

// Author: Max Jakob (max.jakob@fu-berlin.de)
// Set the user login for the mappings wiki.

require_once '../../include.php';

session_start();

if(Tht_Helper_Parameter::hasPOST('username') && Tht_Helper_Parameter::hasPOST('password')){

    $_SESSION["username"] = Tht_Helper_Parameter::POST('username');
    $_SESSION["password"] = Tht_Helper_Parameter::POST('password');

    // check login
    $mwuser = new Tht_MediaWiki_User(
        $_SESSION["username"],
        $_SESSION["password"]
    );
    
    //Zend_Registry::get('logger')->log("(setuser.php) user name is set to: ".$_SESSION["username"], 5);
        
    $apiUrl = Zend_Registry::get('config')->dbpedia->api->url;
    $mwd = new Tht_MediaWiki_DBpedia($apiUrl);
    
    $isValid = $mwd->isValidLogin($mwuser);
    
    Tht_Helper_Header::JS();
    if ($isValid){
        echo json_encode(array(
            'success' => true,
            'username' => $_SESSION["username"]
        ));
    }
    else {
        echo json_encode(array(
            'success' => false,
            'message' => "Invalid username or password for mappings.dbpedia.org"
        ));
    }
}
else {
    Tht_Helper_Header::JS();
    echo json_encode(array(
        'success' => false,
        'message' => "Username and/or password were not posted to api/setuser.php."
    ));
}