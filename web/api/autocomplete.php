<?php

if(!defined('__INCLUDE_LOADED__')){
    die('please include "include.php" for functionality');
}

/**
 * run wikipedia reader
 */

if (Tht_Helper_Parameter::hasPOST('query')){
    $apiUrl = Zend_Registry::get('config')->wikipedia->api->url;

    // initialize cache
    $frontendOptions = array(
        'lifetime'                => Zend_Registry::get('config')->wikipedia->cache->api->autocomplete->lifetime,
        'automatic_serialization' => true
    );
    $backendOptions  = array(
        'cache_dir' => __CACHE__
    );
    $cache = Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);

    $cacheId = md5(__FILE__ . Tht_Helper_Parameter::POST('query'));
    if(!Zend_Registry::get('config')->wikipedia->cache->api->autocomplete->enable || !($pages = $cache->load($cachId))){
        $mww = new Tht_MediaWiki_Wikipedia($apiUrl);
        $pages = $mww->getSuggestedPagesByTitle(Tht_Helper_Parameter::POST('query'));
        
        if(Zend_Registry::get('config')->wikipedia->cache->api->autocomplete->enable) {
            $cache->save($pages, $cacheId);
        }
    }
    
    Tht_Helper_Header::JS();
    //header('Content-type: text/javascript;charset=utf-8');
    echo $pages;
} else {
    Tht_Helper_Header::badRequest();
    //header('HTTP/1.1 400 Bad Request');
    die();
}