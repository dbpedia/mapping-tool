<?php

class Tht_Helper_Header
{

    public static function cacheJS(array $files=array())
    {
        // check for the newest file
        // set the lastmodified timestamp to the
        // latest edited file
        if($files === array()){
            $debug = debug_backtrace();
            $file = $debug[0]['file'];
            $time = filemtime($file);
        } else {
            $time = strtotime("1 January 2010");
            foreach($files as $file){
                $filemtime = filemtime($file);
                if($filemtime > $time){
                    $time = $filemtime;
                }
            }
        }
        
        // set Content-Type to javascript
        header('Content-Type: text/javascript; charset=UTF-8');
        
        // set current Date
        $curTimestamp = gmdate('D, d M Y H:i:s', strtotime('1 May 2010')) . ' GMT';
        header('Date: ' . $curTimestamp );

        // set last modified date (see above for $time)
        $lastmodified = gmdate('D, d M Y H:i:s', $time) . ' GMT';
        header('Last-Modified: ' . $lastmodified );

        // set the expire date 1 year in the future
        $futTimestamp = gmdate('D, d M Y H:i:s', strtotime('+1 year')) . ' GMT';
        header('Expires: ' . $futTimestamp );

        // set the Cache-control
        header('Cache-control: public, max-age=31536000');
        
        // check if 304 Not Modified can be send without any
        // html in the body
        if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && $_SERVER['HTTP_IF_MODIFIED_SINCE'] == $lastmodified){
            header('HTTP/1.1 304 Not Modified');
            
            // no need for further browser output, the browser
            // provides the user with cached content
            die();
        }
    }

    public static function JS()
    {
        header('Content-Type: text/javascript; charset=UTF-8');
    }

    public static function badRequest()
    {
         header('HTTP/1.1 400 Bad Request');
    }

}