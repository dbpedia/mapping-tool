<?php

// define ENVIRONENT
define('ENVIRONMENT', 'production');

// define absolute path of root directory
define('__ROOT__', realpath(dirname(__FILE__) . '/..'));

// define absolute path of cache directory
define('__CACHE__', __ROOT__ . '/tmp/');

// define include.php loaded flag
define('__INCLUDE_LOADED__', true);

// register Zend autoloader
set_include_path(
      __ROOT__ . '/lib/vendor'
    //. PATH_SEPARATOR
    //. __ROOT__ . '/lib/vendor/doctrine'
    . PATH_SEPARATOR
    . get_include_path()
);
require_once __ROOT__ . '/lib/vendor/Zend/Loader/Autoloader.php';

require_once __ROOT__ . '/lib/vendor/Tht/MediaWiki/Reader/Core.php';
require_once __ROOT__ . '/lib/vendor/Tht/MediaWiki/DBpedia.php';

$autoloader = Zend_Loader_Autoloader::getInstance();

// register namespace for user library
$autoloader->registerNamespace('Tht');

// enable this, when you want to use the set_include_path()
//$autoloader->registerNamespace('Doctrine');

// load Doctrine Object Relational Mapper
require_once __ROOT__ . '/lib/vendor/doctrine/Doctrine.php';
// register Doctrine ORM class loader
spl_autoload_register(array('Doctrine', 'autoload'));
spl_autoload_register(array('Doctrine', 'modelsAutoload'));
$lang= isset($_GET["lang"])?$_GET["lang"]:$_POST["lang"];

// create config from scratch or cache
// depending on ENVIRONMENT
$frontendOptions = array(
    'lifetime'                => 1800,
    'automatic_serialization' => true
);
$backendOptions  = array(
    'cache_dir' => __CACHE__
);
$cache = Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);
$cacheID = 'config';
if(!($config = $cache->load($cacheID)) || ENVIRONMENT !== 'production'||isset($lang)){

     $config = new Zend_Config_Ini(__ROOT__ . '/config/config.ini', ENVIRONMENT,true);
    if(isset($lang)){

     
 /*
     if(!file_exists(__ROOT__ . '/config/i18n/'.$lang.'/lang.ini')){
          $lang='en';
      }
*/
    }
    else $lang='en';
    //$config->merge(new Zend_Config_Ini(__ROOT__ . '/config/i18n/'.$lang.'/lang.ini', ENVIRONMENT));
    $config->setReadOnly();
    $cache->save($config, $cacheID);

}

Zend_Registry::set('config', $config);




// adding a logger to registry
$format    = '%timestamp% %priorityName% (%priority%): %message%' . PHP_EOL;
$formatter = new Zend_Log_Formatter_Simple($format);

$logFile = $config->tool->logging->file;
$logFile = str_replace('{date}', date('Y-m-d'), $logFile);

$writer = new Zend_Log_Writer_Stream( __ROOT__ . '/' . $logFile );
$writer->setFormatter($formatter);

$logger = new Zend_Log();
$logger->addWriter($writer);

// if logging is disabled use a Null-Writer
// as logger
if(!($config->tool->enable->debug)){
    $writer = new Zend_Log_Writer_Null;
    $logger = new Zend_Log($writer);
}

Zend_Registry::set('logger', $logger);

/**
How To Use The Log

EMERG   = 0;  // Emergency: system is unusable
ALERT   = 1;  // Alert: action must be taken immediately
CRIT    = 2;  // Critical: critical conditions
ERR     = 3;  // Error: error conditions
WARN    = 4;  // Warning: warning conditions
NOTICE  = 5;  // Notice: normal but significant condition
INFO    = 6;  // Informational: informational messages
DEBUG   = 7;  // Debug: debug messages

$logger->addPriority('FOO', 8);

$logger->log('Foo message', 8);
$logger->foo('Foo Message');
**/


// change local settings of php.ini depending on ENVIRONMENT
foreach($config->system->php->ini as $key => $value){
    ini_set($key, $value);
}

// set the default timezone
ini_set('date.timezone', 'Europe/Berlin');

// define parser tokens
foreach($config->tool->parser->token as $key => $value){
    define($key, $value);
}

// define PREFIX
define('PREFIX', $config->tool->prefix->PREFIX);

 //initialize language aliases
$wr = new Tht_MediaWiki_DBpedia($config->dbpedia->api->url);
$language = $wr->getLanguageByName($lang);
Zend_Registry::set('language', $language);
//var_dump($language);

// initialize Database settings
// initialize Doctrine ORM with data
$driver   = Zend_Registry::get('config')->database->params->driver;
$database = Zend_Registry::get('config')->database->params->dbname;
$host     = Zend_Registry::get('config')->database->params->host;
$port     = Zend_Registry::get('config')->database->params->port;
$username = Zend_Registry::get('config')->database->params->username;
$password = Zend_Registry::get('config')->database->params->password;

$manager  = Doctrine_Manager::getInstance();
$conn     = Doctrine_Manager::connection("{$driver}://{$username}:{$password}@{$host}:{$port}/{$database}", 'conn_name');

// adjust Doctrine ORM
// see http://www.doctrine-project.org/documentation/manual/1_2/en/configuration
$manager->setAttribute(Doctrine::ATTR_VALIDATE,               Doctrine::VALIDATE_ALL);
$manager->setAttribute(Doctrine::ATTR_EXPORT,                 Doctrine::EXPORT_ALL);
$manager->setAttribute(Doctrine::ATTR_MODEL_LOADING,          Doctrine::MODEL_LOADING_CONSERVATIVE);
$manager->setAttribute(Doctrine::ATTR_QUOTE_IDENTIFIER,       true);
$manager->setAttribute(Doctrine::ATTR_AUTO_ACCESSOR_OVERRIDE, true);

// set query cache
if($config->database->cache->apc){
    $cacheDriver = new Doctrine_Cache_Apc();
    $manager->setAttribute(Doctrine_Core::ATTR_QUERY_CACHE, $cacheDriver);
}

// load ORM models for autoloader
Doctrine::loadModels(__ROOT__ . '/lib/models');

