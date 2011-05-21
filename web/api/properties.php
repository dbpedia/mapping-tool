<?php

if(!defined('__INCLUDE_LOADED__')){
    die('please include "include.php" for functionality');
}

function myPropertySort($a, $b){
  return strcasecmp($a['label'], $b['label']);
}

$load = Tht_Helper_Parameter::POST('load', 'root');
if( $load === 'initial' ){
  echo json_encode(array());
  die();
}

/** array of returned properties **/
$propList = array();

/** select specific onotlogyclass by id **/
if(is_numeric($load)){
  // the given information is the id
  // of an ontology class
  $q = Doctrine_Query::create()
      ->select('oc.*')
      ->from('Ontologyclass oc')
      ->where('oc.id = ?', $load);
  $node = $q->execute();
  $node = $node->toArray(true);
} else {
    // the given information is the name of
    // of an ontology class
    $q = Doctrine_Query::create()
      ->select('oc.*')
      ->from('Ontologyclass oc')
      ->where('oc.name = ?', $load);
  $node = $q->execute();
  $node = $node->toArray(true);
}

/** object properties **/
$q = Doctrine_Query::create()
    ->select('oc.name, oc.label, p.*')          // select properties for ontologyclasses
    ->from('Ontologyclass oc')                  // from ontologyclasses
    ->leftJoin('oc.Objectproperties p')         // join object properties table
    ->where('oc.lft <= ?', $node[0]['lft'])     // select parents properties too
    ->andWhere('oc.rgt >= ?', $node[0]['rgt']); // select parents properties too
//    ->andWhere('oc.level > ?', 0);            // do not select root node
    
$objectProperties = $q->execute();

$propertyHolders = $objectProperties->toArray(true);

foreach($propertyHolders as $ontologyClass){
  foreach($ontologyClass['Objectproperties'] as $property){
    $tmp = array();
    $tmp['name']     = $property['name'];
    $tmp['label']    = $property['label'];
    $tmp['oclass']   = $ontologyClass['name'];
    //$tmp['text']     = $ontologyClass['label'] . ': ' . '<b>' . $property['label'] . '</b> <i>' . $property['rangename']. '</i>';
    $tmp['text']     = '<b>' . $property['label'] . '</b> [' . $ontologyClass['label'] . '] <i>' . $property['rangename']. '</i>';
    $tmp['leaf']     = true;
    $tmp['iconCls']  = 'my-tree-icon-' . PREFIX . 'OntologyProperty';
    //$tmp['nodeType'] = PREFIX . 'OntologyProperty';
    $tmp['type']     = 'OntologyProperty';
    
    $propList[] = $tmp;
  }
}

/** datatype properties **/
$q = Doctrine_Query::create()
    ->select('oc.name, oc.label, p.*')          // select properties for ontologyclasses
    ->from('Ontologyclass oc')                  // from ontologyclasses
    ->leftJoin('oc.Datatypeproperties p')       // join datatyope properties table
    ->where('oc.lft <= ?', $node[0]['lft'])     // select parents properties too
    ->andWhere('oc.rgt >= ?', $node[0]['rgt']); // select parents properties too
//    ->andWhere('oc.level > ?', 0);            // do not select root node
    
$datatypeProperties = $q->execute();

$propertyHolders = $datatypeProperties->toArray(true);

foreach($propertyHolders as $ontologyClass){
  foreach($ontologyClass['Datatypeproperties'] as $property){
    $tmp = array();
    $tmp['name']    = $property['name'];
    $tmp['label']   = $property['label'];
    $tmp['oclass']  = $ontologyClass['name'];
    //$tmp['text']    = $ontologyClass['label'] . ': ' . '<b>' . $property['label'] . '</b> <i>' . $property['rangename']. '</i>';
    $tmp['text']    = '<b>' . $property['label'] . '</b> [' . $ontologyClass['label'] . '] <i>' . $property['rangename']. '</i>';
    //$tmp['uri']     = $property['uri'];
    $tmp['leaf']    = true;
    $tmp['iconCls'] = 'my-tree-icon-' . PREFIX . 'OntologyProperty';
    //$tmp['nodeType'] = PREFIX . 'OntologyProperty';
    $tmp['type']     = 'OntologyProperty';
    
    $propList[] = $tmp;
  }
}

/** sort properties **/
usort($propList, 'myPropertySort');

Tht_Helper_Header::JS();
//header('Content-type: application/javascript');
/** print properties in json format **/
echo json_encode($propList);