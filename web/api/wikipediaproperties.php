<?php

if(!defined('__INCLUDE_LOADED__')){
    die('please include "include.php" for functionality');
}


if (Tht_Helper_Parameter::hasGET('titles')){
   $lang=Zend_Registry::get('language');
  $apiUrl = $lang["wikipediaAPIURL"];

  $wr = new Tht_MediaWiki_Wikipedia($apiUrl);
  $markup = $wr->getMarkupByTitle(Tht_Helper_Parameter::GET('titles'));
  
  header('Content-type: text/javascript;charset=utf-8');
  echo $markup;
} else {
  header('HTTP/1.1 400 Bad Request');
  die();
}