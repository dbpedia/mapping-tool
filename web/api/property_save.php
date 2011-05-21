<?php

if(!defined('__INCLUDE_LOADED__')){
    die('please include "include.php" for functionality');
}

if(Tht_Helper_Parameter::hasPOST('titles') && Tht_Helper_Parameter::hasPOST('text')){
    
    Tht_Dml_Grammar::loadGrammarFile(__ROOT__ . '/grammar/dbpedia_elements_grammar.xml');
    
    // check if the property is valid
    try{
        $tree = Tht_Dml_Parser::parse(Tht_Helper_Parameter::POST('text'), __ROOT__ . '/grammar/dbpedia_elements_grammar.xml');
    } catch (Exception $e) {
        Tht_Helper_Header::badRequest();
        Tht_Helper_Header::JS();
        echo json_encode(array('message' => 'property invalid ' . $e->getMessage()));
        die();
    }
    
    $document = new Tht_MediaWiki_Document(
        Tht_Helper_Parameter::POST('titles'),
        Tht_Helper_Parameter::POST('text')
    );
    
    $mwuser = new Tht_MediaWiki_User(
        Zend_Registry::get('config')->dbpedia->user->name,
        Zend_Registry::get('config')->dbpedia->user->password
    );
    
    $apiUrl = Zend_Registry::get('config')->dbpedia->api->url;
    $mwd = new Tht_MediaWiki_DBpedia($apiUrl);
    
    // check if the property already exist
    $checkDocument = $mwd->getMarkupByTitle($document->getTitle());
    if($checkDocument instanceof Tht_MediaWiki_Document){
        Tht_Helper_Header::badRequest();
        echo json_encode(array('message' => 'property already exist', 'newTitle' => $checkDocument->getTitle(), 'newMarkup' => $checkDocument->getText(), 'currentMarkup' => $document->getText()));
        
        // cancel saving process
        die();
    }
    
    // save document
    $mwd->login($mwuser);
    $mwd->saveDocument($document);
}

Tht_Helper_Header::JS();
echo json_encode(array('message' => 'property successfully saved'));