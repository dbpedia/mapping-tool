<?php
if(!defined('__INCLUDE_LOADED__')){
    die('please include "include.php" for functionality');
}

// check for key parameter
$key = Tht_Helper_Parameter::REQUEST('key', false);
if(!$key){
    Tht_Helper_Header::JS();
    Tht_Helper_Header::badRequest();
    echo json_encode(array(
        'message' => 'missing parameter "key"'
    ));
    die();
}

// check for valid password
if(   $key !== Zend_Registry::get('config')->sync->ontology->password
   && Zend_Registry::get('config')->sync->ontology->usePassword){
    Tht_Helper_Header::JS();
    Tht_Helper_Header::badRequest();
    echo json_encode(array(
        'message' => 'invalid key'
    ));
    die();
}

// MediaWiki namespaces
define('NS_DBPEDIA_CLASS',    Zend_Registry::get('config')->dbpedia->ns->NS_DBPEDIA_CLASS    );
define('NS_DBPEDIA_PROPERTY', Zend_Registry::get('config')->dbpedia->ns->NS_DBPEDIA_PROPERTY );
define('NS_DBPEDIA_MAPPING',  Zend_Registry::get('config')->dbpedia->ns->NS_DBPEDIA_MAPPING  );
define('NS_DBPEDIA_DATATYPE', Zend_Registry::get('config')->dbpedia->ns->NS_DBPEDIA_DATATYPE );

// init a cache for DBpedia
$frontendOptions = array(
    'lifetime'                => Zend_Registry::get('config')->dbpedia->cache->api->lifetime,
    'automatic_serialization' => true
);
$backendOptions  = array(
    'cache_dir' => __CACHE__
);
$cache = Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);

// create MediaWiki reader for DBpedia
$apiUrl = Zend_Registry::get('config')->dbpedia->api->url;
$mwd    = new Tht_MediaWiki_DBpedia($apiUrl);

// check for cache and load ontology classes
$cacheId = md5(__FILE__ . NS_DBPEDIA_CLASS);
if( !Zend_Registry::get('config')->dbpedia->cache->api->enable || !($classTemplates = $cache->load($cacheId))){
    $classTemplates = $mwd->getPageContentByNamespace(NS_DBPEDIA_CLASS);

    if(Zend_Registry::get('config')->dbpedia->cache->api->enable){
        $cache->save($classTemplates, $cacheId);
    }
}

// check for cache and load ontology object properties
$cacheId = md5(__FILE__ . NS_DBPEDIA_PROPERTY);
if( !Zend_Registry::get('config')->dbpedia->cache->api->enable || !($propertyTemplates = $cache->load($cacheId))){
    $propertyTemplates = $mwd->getPageContentByNamespace(NS_DBPEDIA_PROPERTY);

    if(Zend_Registry::get('config')->dbpedia->cache->api->enable){
        $cache->save($propertyTemplates, $cacheId);
    }
}

// check for cache and load ontology database properties
$cacheId = md5(__FILE__ . NS_DBPEDIA_DATATYPE);
if( !Zend_Registry::get('config')->dbpedia->cache->api->enable || !($datatypeTemplates = $cache->load($cacheId))){
    $datatypeTemplates = $mwd->getPageContentByNamespace(NS_DBPEDIA_DATATYPE);

    if(Zend_Registry::get('config')->dbpedia->cache->api->enable){
        $cache->save($datatypeTemplates, $cacheId);
    }
}

// check for cache and templates
$cacheId = md5(__FILE__ . 10);
if( !Zend_Registry::get('config')->dbpedia->cache->api->enable || !($templates = $cache->load($cacheId))){
    $templates = $mwd->getPageContentByNamespace(10);

    if(Zend_Registry::get('config')->dbpedia->cache->api->enable){
        $cache->save($templates, $cacheId);
    }
}

//echo '<pre>';
//print_r($classTemplates);
//print_r($propertyTemplates);
//print_r($datatypeTemplates);
//echo '</pre>';


// // drop existing database via Doctrine ORM
// // normally not working cause of insufficient rights
// $manager->dropDatabases();

// // create new database via Doctrine ORM
// // normally not working cause of insufficient rights
// $manager->createDatabases();

// iterate over db tables and delete them
$tables = $conn->import->listTables();
foreach($tables as $table){
    $conn->export->dropTable($table);
}

// generate models from config/ontology.yml table definition file for Doctrine ORM
// and save the resulting files to lib/models
Doctrine::generateModelsFromYaml(__ROOT__ . '/config/ontology.yml', __ROOT__ . '/lib/models');

// create database tables from php (orm) table representations in lib/models
Doctrine::createTablesFromModels(__ROOT__ . '/lib/models');

// start db transaction for import of data
$conn->beginTransaction();

/**
 * iterate on ontology classes
 * and add each to a custom store
 */
$classes = array();

foreach($classTemplates as $classTemplate){
    // create ontology class object from wiki markup
    $class = Tht_Import_ClassFactory::createClassFromWikiMarkup($classTemplate);

    // check if class is a correct instance
    // otherwise log error
    if(!($class instanceof Tht_Import_Class)){
        Zend_Registry::get('logger')->log($classTemplate, 5);
        continue;
    }

    $classes[] = $class;

    // add class to custom store
    Tht_Import_Store::addClass($class);
}

/**
 * iterate over all ontology classes in the
 * store and reorder them to retrieve
 * a hierarchical ontology class tree
 */
foreach($classes as $class){
    // if class is a child class
    // define parent class
    if($class->hasParent()){
        $parent = $class->getParentName();

        // reorder store
        Tht_Import_Store::addChildToClass($parent, $class->name);
        Tht_Import_Store::addParentToClass($class->name, $parent);
    }
}

/**
 * create root node owl:Thing
 */
$rootNode = new Ontologyclass();
$rootNode->name  = 'owl:Thing';
$rootNode->label = 'owl:Thing';
$rootNode->save();

/**
 * create tree model for database
 */
$table = Doctrine::getTable('Ontologyclass');
$tree  = $table->getTree();
$tree->createRoot($rootNode);

/**
 * save tree model in the store
 * works recursively
 */
Tht_Import_Store::save($rootNode);

// add $property data
foreach($propertyTemplates as $propertyTemplate){
    // create property object from wiki markup
    Tht_Import_PropertyFactory::createPropertyFromWikiMarkup($propertyTemplate);
}

// add datatype data
foreach($datatypeTemplates as $datatypeTemplate){
    // create datatype object from wiki markup
    Tht_Import_DatatypeFactory::createDatatypeFromWikiMarkup($datatypeTemplate);
}

// commit db transaction
$conn->commit();

Tht_Helper_Header::JS();
echo json_encode(array(
    'message' => 'ontology refreshed'
));