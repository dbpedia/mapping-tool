<?php

/**
 * run wikipedia reader
 */

 if(!defined('__INCLUDE_LOADED__')){
    die('please include "include.php" for functionality');
    //require_once 'include.php';
}

if (Tht_Helper_Parameter::hasGET('titles')){
    $apiUrl = Zend_Registry::get('config')->wikipedia->api->url;
    
    $wr = new Tht_MediaWiki_Wikipedia($apiUrl);
    $json = $wr->getRandomExampleUrlByTemplate(Tht_Helper_Parameter::GET('titles'));
    
    Tht_Helper_Header::JS();
    echo $json;
} else {
    Tht_Helper_Header::badRequest();
    die();
}