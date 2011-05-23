<?php

/**
 * run wikipedia reader
 */

require_once 'include.php';

if (Tht_Helper_Parameter::hasGET('titles')){
   $lang=Zend_Registry::get('language');
   $apiUrl = $lang["wikipediaAPIURL"];
    
    $wr = new Tht_MediaWiki_Wikipedia($apiUrl);
    $json = $wr->getRandomExampleUrlByTemplate(Tht_Helper_Parameter::GET('titles'));
    
    Tht_Helper_Header::JS();
    echo $json;
} else {
    Tht_Helper_Header::badRequest();
    die();
}