<?php

if(!defined('__INCLUDE_LOADED__')){
    die('please include "include.php" for functionality');
}

$title     = Tht_Helper_Parameter::GET('title', false);
$namespace = Tht_Helper_Parameter::GET('namespace', false);

if(!$title || !$namespace){
    header('Content-type: application/javascript');
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(array(
        'message' => 'missing parameter "title" or "namespace"'
    ));
    die();
}



// delete OntologyClass: namespace changes
if( $namespace == Zend_Registry::get('config')->dbpedia->ns->NS_DBPEDIA_CLASS ){
    $class = Doctrine::getTable('Ontologyclass')->findOneByName(str_replace('OntologyClass:', '', $title));
    if($class instanceof Ontologyclass){
        $class->getNode()->delete();
    }
}

// delete OntologyProperty: namespace changes ObjectProperty and Datatypeproperty
if( $namespace == Zend_Registry::get('config')->dbpedia->ns->NS_DBPEDIA_PROPERTY ){
    $property = Doctrine::getTable('Datatypeproperty')->findOneByName(str_replace('OntologyProperty:', '', $title));
    if($property instanceof Datatypeproperty){
        $property->delete();
    }
    
    $property = Doctrine::getTable('Objectproperty')->findOneByName(str_replace('OntologyProperty:', '', $title));
    if($property instanceof Objectproperty){
        $property->delete();
    }
}

// delete Datatype: namespace changes
if( $namespace == Zend_Registry::get('config')->dbpedia->ns->NS_DBPEDIA_DATATYPE ){
    $datatype = Doctrine::getTable('Datatype')->findOneByName(str_replace('Datatype:', '', $title));
    if($datatype instanceof Datatype){
        $datatype->delete();
    }
}
