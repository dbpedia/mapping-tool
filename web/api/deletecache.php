<?php

if(!defined('__INCLUDE_LOADED__')){
    die('please include "include.php" for functionality');
}

if( $config->cache->delete->allow == true ) {
    if(Tht_Helper_Parameter::hasREQUEST('pass') && Tht_Helper_Parameter::REQUEST('pass') == $config->cache->delete->password){
        
        $fileList = glob(__ROOT__ . '/tmp/zend_cache*');
        foreach($fileList as $file){
            unlink($file);
        }
        
        echo 'cache cleared';
    } else {
        echo 'cache delete is not allowed';
    }
} else {
    echo 'cache delete is not allowed';
}