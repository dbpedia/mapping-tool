<?php

if(!defined('__INCLUDE_LOADED__')){
    die('please include "include.php" for functionality');
}

$title = Tht_Helper_Parameter::GET('title', false);

if(!$title){
    Tht_Helper_Header::JS();
    Tht_Helper_Header::badRequest();
    echo json_encode(array(
        'message' => 'missing parameter "title"'
    ));
    die();
}

// create MediaWiki reader for DBpedia
$apiUrl = Zend_Registry::get('config')->dbpedia->api->url;
$mwd    = new Tht_MediaWiki_DBpedia($apiUrl);

$page = $mwd->getMarkupByTitle($title);

if($page instanceof Tht_MediaWiki_Document){

    // update OntologyClass: namespace changes
    if(    $page->getNamespace() == Zend_Registry::get('config')->dbpedia->ns->NS_DBPEDIA_CLASS
        && $page->getTitle() != ''
    ){

        // simulate a wikipedia answer for Tht_Import_ClassFactory
        $class = Tht_Import_ClassFactory::createClassFromWikiMarkup($page);

        // fetch old class and delete it
        $oldClass = Doctrine::getTable('Ontologyclass')->findOneByName($class->name);
        if($oldClass instanceof Ontologyclass){
            $oldClass->getNode()->delete();
        }

        // check if class has parent
        if($class->parentName !== null && $class->parentName != ''){
            // fetch parent and set class as child
            $parentClass = Doctrine::getTable('Ontologyclass')->findOneByName($class->parentName);
            $class->getNode()->insertAsLastChildOf($parentClass);
        } else {
            // fetch root and set class as child
            $rootNode = Doctrine::getTable('Ontologyclass')->findOneById(1);
            $class->getNode()->insertASLastChildOf($rootNode);
        }

    }


    // update OntologyProperty: namespace changes ObjectProperty and Datatypeproperty
    if(    $page->getNamespace() == Zend_Registry::get('config')->dbpedia->ns->NS_DBPEDIA_PROPERTY
        && $page->getTitle()
    ){
        $op = Doctrine::getTable('Objectproperty')->findOneByName(str_replace('OntologyProperty:', '', $page->getTitle()));
        if($op instanceof Objectproperty){
            Zend_Registry::get('logger')->log('object property deleted ' . $op->name, 5);
            $op->delete();
        } else {
            $dp = Doctrine::getTable('Datatypeproperty')->findOneByName(str_replace('OntologyProperty:', '', $page->getTitle()));
            if($dp instanceof Datatypeproperty){
                Zend_Registry::get('logger')->log('datatype property deleted ' . $dp->name, 5);
                $dp->delete();
            }
        }
        
        Zend_Registry::get('logger')->log('property changed 2', 5);
        Tht_Import_PropertyFactory::createPropertyFromWikiMarkup($page);
        
    }
    
    
    
    
    // update Datatype: namespace changes
    if(    $page->getNamespace() == Zend_Registry::get('config')->dbpedia->ns->NS_DBPEDIA_DATATYPE
        && $page->getTitle()
    ){

        $dt = Doctrine::getTable('Datatype')->findOneByName(str_replace('Datatype:', '', $page->getTitle()));
        if($dt instanceof Datatype){
            Zend_Registry::get('logger')->log('datatype deleted ' . $dt->name, 5);
            $dt->delete();
        }
        Tht_Import_DatatypeFactory::createDatatypeFromWikiMarkup($page);
    }

}