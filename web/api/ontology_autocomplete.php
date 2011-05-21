<?php

if(!defined('__INCLUDE_LOADED__')){
    die('please include "include.php" for functionality');
}

function arrangeMatches($rows){
    $out = array();
    foreach($rows as $row){
        $out[] = array(
            'name' => $row->name,
            'label' => $row->label
        );
    }
    
    return json_encode(array(
        'total' => count($out),
        'data'  => $out
    ));
}

if (Tht_Helper_Parameter::hasPOST('query')){
    
    
    $q = Doctrine_Query::create()
      ->from('Ontologyclass o')
      ->where('o.name LIKE ?', array('%' . Tht_Helper_Parameter::POST('query') . '%'))
      ->orderBy('o.name')
      ->limit(25);
    $rows = $q->execute();
    
    Tht_Helper_Header::JS();
    echo arrangeMatches($rows);
} else {
    Tht_Helper_Header::badRequest();
    //header('HTTP/1.1 400 Bad Request');
    die();
}