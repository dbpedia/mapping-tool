<?php

if(!defined('__INCLUDE_LOADED__')){
    die('please include "include.php" for functionality');
}



  $apiUrl = Zend_Registry::get('config')->dbpedia->api->url;

  $wr = new Tht_MediaWiki_DBpedia($apiUrl);
  $markup = Array();

  $markup["languages"] = $wr->getLanguageNamespaces();
  

  echo json_encode($markup);
