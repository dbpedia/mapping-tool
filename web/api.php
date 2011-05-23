<?php

/**
 * This file handles remote calls of dbpedia
 * to synchronize ontology
 *
 * + fetching pageids|namespace to refresh|update ontology
 */

require_once 'include.php';

$defaultValue = null;
switch(Tht_Helper_Parameter::REQUEST('action', $defaultValue)){

    // update a single ontology element
    case 'update':
        require_once 'api/update.php';
        break;

    // delete a single ontology element
    case 'delete':
        require_once 'api/delete.php';
        break;

    // update the whole ontology
    case 'updateall':
        require_once 'api/updateall.php';
        break;

    // log a call (testing Hooks with MediaWiki)
    case 'log':
        require_once 'api/log.php';
        break;
    
    // to test whether the api works
    case 'test':
        require_once 'api/test.php';
        break;
    
    // load the template tree
    case 'template':
        require_once 'api/template.php';
        break;
       
    // load the template tree
    case 'unit':
        require_once 'api/unit.php';
        break;
    
    // save a property
    case 'datatype_save':
        require_once 'api/datatype_save.php';
        break;
    
    // use the mapper
    case 'map':
        require_once 'api/map.php';
        break;
    
    // load ontology classes
    case 'ontology':
        require_once 'api/ontology.php';
        break;
        
    // load ontology classes
    case 'ontology_autocomplete':
        require_once 'api/ontology_autocomplete.php';
        break;
    
    // save an ontology classes
    case 'ontologyclass_save':
        require_once 'api/ontologyclass_save.php';
        break;
        
    // load autocomplete
    case 'autocomplete':
        require_once 'api/autocomplete.php';
        break;
        
    // load properties
    case 'properties':
        require_once 'api/properties.php';
        break;
    
    // save a property with check of existance
    case 'property_save':
        require_once 'api/property_save.php';
        break;
    
    // saves a property without checking existance
    case 'property_save_force':
        require_once 'api/property_save_force.php';
        break;
    
    // save a mapping
    case 'mapping_save':
        require_once 'api/mapping_save.php';
        break;

    case 'remotevalidate':
        require_once 'api/remotevalidate.php';
        break;
    
    case 'wikipediaproperties':
        require_once 'api/wikipediaproperties.php';
        break;
        
    case 'deletecache':
        require_once 'api/deletecache.php';
        break;
    
    case 'examples':
        require_once 'api/exampleArticles.php';
        break;
    case 'languages':
        require_once 'api/languages.php';
        break;
    
    // no given action
    default:
        Tht_Helper_Header::badRequest();
        $sxe = new SimpleXMLElement('<error><message>action unknown</message></error>');
        header('Content-type: text/xml');
        echo $sxe->asXml();
}