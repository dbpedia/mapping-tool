<?php

if(!defined('__INCLUDE_LOADED__')){
    die('please include "include.php" for functionality');
}

if(Tht_Helper_Parameter::hasPOST('titles') && Tht_Helper_Parameter::hasPOST('text')){
    
    Tht_Dml_Grammar::loadGrammarFile(__ROOT__ . '/grammar/dbpedia_mapping_grammar.xml');
    
    if(Zend_Registry::get('config')->tool->validation->local->enable == true) {
        try{
            $tree = Tht_Dml_Parser::parse(Tht_Helper_Parameter::POST('text'), __ROOT__ . '/grammar/dbpedia_mapping_grammar.xml');
        } catch (Exception $e) {
            Tht_Helper_Header::badRequest();
            Tht_Helper_Header::JS();
            echo json_encode(array('message' => 'mapping invalid', 'errors' => $e->getMessage()));
            die();
        }
    }
    
    if(Zend_Registry::get('config')->tool->validation->remote->enable == true) {
        $rmv = new Tht_Dml_RemoteValidator();
        $rmv->validateMarkup(Tht_Helper_Parameter::POST('titles'), Tht_Helper_Parameter::POST('text'));
        
        if(!$rmv->isValid()){
            Tht_Helper_Header::badRequest();
            Tht_Helper_Header::JS();
            echo json_encode(array('message' => 'remote validation failed<br><hr>' . $rmv->getHtmlListOfErrors()));
            die();
        }
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
    $mwd->login($mwuser);
    
    $mwd->saveDocument($document);
}

Tht_Helper_Header::JS();
echo json_encode(array('message' => 'mapping successfully saved'));