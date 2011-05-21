<?php

if(!defined('__INCLUDE_LOADED__')){
    die('please include "include.php" for functionality');
}

if(Tht_Helper_Parameter::hasPOST('titles') && Tht_Helper_Parameter::hasPOST('text')){
    
    $rmv = new Tht_Dml_RemoteValidator();
    $result = $rmv->validateMarkup(Tht_Helper_Parameter::POST('titles'), Tht_Helper_Parameter::POST('text'));
    
    if(!$rmv->isValid()){
        Tht_Helper_Header::badRequest();
        echo json_encode(array('message' => 'remote validation failed', 'errors' => $rmv->getHtmlListOfErrors()));
        die();
    }
    
    Tht_Helper_Header::JS();
    echo json_encode(array('message' => 'mapping is valid'));
    die();
}

Tht_Helper_Header::badRequest();
echo json_encode(array('message' => 'missing parameters', 'errors' => 'n.a.'));