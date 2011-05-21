<?php
/**
 * proxy to access the globals
 * $_GET, $_POST, $_REQUEST
 * - supports default/fallback values
 * - optionally throws exceptions when values missing
 */
class Tht_Helper_Parameter
{
    public static function GET($key, $default=null, $throwException=false){
        return self::hasGET($key, $throwException) ? $_GET[$key] : $default;
    }

    public static function hasGET($key, $throwException=false){
        if($throwException){
            if(!isset($_GET[$key])){
                throw new Exception('missing _GET parameter "' . htmlentities($key) . '"');
            }
        }
        return isset($_GET[$key]);
    }
    
    public static function POST($key, $default=null, $throwException=false){
        return self::hasPOST($key, $throwException) ? $_POST[$key] : $default;
    }
    
    public static function hasPOST($key, $throwException=false){
        if($throwException){
            if(!isset($_POST[$key])){
                throw new Exception('missing _POST parameter "' . htmlentities($key) . '"');
            }
        }
        return isset($_POST[$key]);
    }

    public static function REQUEST($key, $default=null, $throwException=false){
        return self::hasREQUEST($key, $throwException) ? $_REQUEST[$key] : $default;
    }

    public static function hasREQUEST($key, $throwException=false){
        if($throwException){
            if(!isset($_REQUEST[$key])){
                throw new Exception('missing _REQUEST parameter "' . htmlentities($key) . '"');
            }
        }
        return isset($_REQUEST[$key]);
    }
}