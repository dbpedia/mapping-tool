<?php

if(!defined('__INCLUDE_LOADED__')){
    die('please include "include.php" for functionality');
}

Zend_Registry::get('logger')->log(json_encode($_REQUEST), 5);

Tht_Helper_Header::JS();
echo json_encode(array('message' => 'logged'));