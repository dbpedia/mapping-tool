<?php

if(!defined('__INCLUDE_LOADED__')){
    die('please include "include.php" for functionality');
}

if(   Tht_Helper_Parameter::hasPOST('load')
   && Tht_Helper_Parameter::POST('load') != 'initial'
   && Tht_Helper_Parameter::POST('load') != ''){
  $node = Tht_Helper_Parameter::POST('load');
  
  $q = Doctrine_Query::create()
    ->from('Ontologyclass o')
    ->where('o.name LIKE ?', array('%' . $node . '%'));
  $rows = $q->execute();

  echo getJsonByRows($rows);
  die();
}

function getJsonByRows($rows){
  $out = array();

  foreach($rows as $class){
    $tmp = array();
    $tmp['id']      = $class['id'];
    $tmp['value']   = $class['name'];
    $tmp['name']    = $class['name'];
    $tmp['label']   = $class['name'];
    $tmp['text']    = $class['label'];
    //$tmp['nodeType']  = 'Class';
    $tmp['type']    = 'OntologyClass';
    //$tmp['uri']     = $class['uri'];
    $tmp['iconCls'] = 'my-tree-icon-DBpediaOntologyClass';
    
    //if( ($class['lft']+1) == $class['rgt'] ){
      $tmp['leaf'] = true;
    //}

    $out[] = $tmp;
  }

  return json_encode($out);
}

//########################################################

if(!Tht_Helper_Parameter::hasPOST('node') or !is_numeric(Tht_Helper_Parameter::POST('node'))){
  $node = 1;
} else {
  $node = intval(Tht_Helper_Parameter::POST('node'));
}

$treeObject          = Doctrine::getTable('Ontologyclass');
$requestedNode       = $treeObject->findOneById($node);
$requestedChildNodes = $requestedNode->getNode()->getChildren();

Tht_Helper_Header::JS();
//header('Content-type: text/javascript;charset=utf-8');
echo getJsonByNode($requestedChildNodes);


function getJsonByNode($requestedChildNodes){
  $out = array();

  foreach($requestedChildNodes as $class){
    $tmp = array();
    $tmp['id']      = $class['id'];
    $tmp['value']   = $class['name'];
    $tmp['name']    = $class['name'];
    $tmp['label']   = $class['name'];
    $tmp['text']    = $class['label'];
    $tmp['type']    = 'OntologyClass';
    //$tmp['uri']     = $class['uri'];
    $tmp['iconCls'] = 'my-tree-icon-DBpediaOntologyClass';
    if( !$class->getNode()->hasChildren() ){
      $tmp['leaf'] = true;
    }

    $out[] = $tmp;
  }

  return json_encode($out);
}