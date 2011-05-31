<?php

if(!defined('__INCLUDE_LOADED__')){
    die('please include "include.php" for functionality');
}

/**
 * run wikipedia reader
 */

if (Tht_Helper_Parameter::hasPOST('query')){
       $lang=Zend_Registry::get('language');
      $wp_apiUrl = $lang["wikipediaAPIURL"];
      $dbp_apiUrl = Zend_Registry::get('config')->dbpedia->api->url;

    // initialize cache
    $frontendOptions = array(
        'lifetime'                => Zend_Registry::get('config')->wikipedia->cache->api->autocomplete->lifetime,
        'automatic_serialization' => true
    );
    $backendOptions  = array(
        'cache_dir' => __CACHE__
    );
    $cache = Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);
    if(Tht_Helper_Parameter::REQUEST('implemented')=="false")
      $cacheId = md5(__FILE__ . 'wp'.Tht_Helper_Parameter::POST('query'));
    else
      $cacheId = md5(__FILE__ .'dbp'. Tht_Helper_Parameter::POST('query'));
    if(!Zend_Registry::get('config')->wikipedia->cache->api->autocomplete->enable || !($pages = $cache->load($cacheId))){
    
        if(Tht_Helper_Parameter::REQUEST('implemented')=="false"){
            $mww = new Tht_MediaWiki_Wikipedia($wp_apiUrl);
            $pages = $mww->getSuggestedTemplatesByTitle(Tht_Helper_Parameter::POST('query'));
        }
        else{
            $dbp = new Tht_MediaWiki_DBpedia($dbp_apiUrl);
            $pages = $dbp->getSuggestedPagesByTitle(Tht_Helper_Parameter::POST('query'));

        }

        
        if(Zend_Registry::get('config')->wikipedia->cache->api->autocomplete->enable) {
            $cache->save($pages, $cacheId);
        }
    }
    
    Tht_Helper_Header::JS();
    //header('Content-type: text/javascript;charset=utf-8');
    //var_dump($lang);
    echo $pages;
} else {
    Tht_Helper_Header::badRequest();
    //header('HTTP/1.1 400 Bad Request');
    die();
}