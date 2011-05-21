<?php

if(!defined('__INCLUDE_LOADED__')){
    die('please include "include.php" for functionality');
}

Tht_Helper_Header::JS();

if(!Tht_Helper_Parameter::hasPOST('load') AND !Tht_Helper_Parameter::hasGET('titles')){
  die('load param not set in post request');
}

if(Tht_Helper_Parameter::hasGET('load') && Tht_Helper_Parameter::GET('load') == 'initial')
{
  echo json_encode(
    array(
      array(
        'text'      => 'Mapping:',
        'name'      => 'root',
        'iconCls'   => 'my-tree-icon-mapper',
        'type'      => 'TemplateMapping',
        'leaf'      => true,
        'deletable' => false,
        'isRoot'    => true
      )
    )
  );
  die();
}

Tht_Dml_Grammar::loadGrammarFile(__ROOT__ . '/grammar/dbpedia_mapping_grammar.xml');

//echo '[{"name":"TemplateMapping","text":"TemplateMapping","nodeType":"AsyncTemplateMapping","iconCls":"my-tree-icon-mapper","children":[{"name":"mapToClass","text":"mapToClass","nodeType":"mapToClass","iconCls":"my-tree-icon-mapper","children":[{"name":"Company","text":"Company","nodeType":"Company","iconCls":"my-tree-icon-mapper","leaf":true}]}]}]';
//die();

if(Tht_Helper_Parameter::hasGET('titles') AND !Tht_Helper_Parameter::hasPOST('load')){
  $apiUrl = Zend_Registry::get('config')->dbpedia->api->url;
  $dr = new Tht_MediaWiki_DBpedia($apiUrl);
  $markup = $dr->getMarkupByTitle(Tht_Helper_Parameter::GET('titles'));
  if($markup instanceof Tht_MediaWiki_Document){
      $markup = $markup->getText();
  }
  
  if($markup == null || $markup == false || strlen($markup) == 0){
    //header('HTTP/1.1 400 Bad Request');
    
      echo json_encode(
        array(
          array(
            'text'      => htmlentities(Tht_Helper_Parameter::GET('titles')),
            'name'      => 'root',
            'iconCls'   => 'my-tree-icon-mapper',
            'type'      => 'TemplateMapping',
            'leaf'      => true,
            'deletable' => false,
            'isRoot'    => true
          )
        )
      );
    
    //echo json_encode(array('message' => 'no markup found'));
    die();
  }
} else {
    $markup = Tht_Helper_Parameter::POST('load');
}


try {
    $markup = preg_replace('~{{\s*DisclaimerMapping\|{{\s*PAGENAMEE\s*}}\s*}}~iu', '', $markup);
    $markup = preg_replace('~^\s*}}~', '', $markup);
    $tree = Tht_Dml_Parser::parse($markup, __ROOT__ . '/grammar/dbpedia_mapping_grammar.xml');
} catch(Exception $e) {
    //header('HTTP/1.1 400 Bad Request');
    Tht_Helper_Header::badRequest();
    //echo $markup;
    echo json_encode(array(
        'message' => $e->getMessage(),
        'raw'     => $markup)
    );
    die();
}

$rootName = Tht_Helper_Parameter::hasGET('titles') ? Tht_Helper_Parameter::GET('titles') : null;

$myTree = new Tht_Dml_TreeBuilder($tree, $rootName);
echo json_encode(array($myTree->getAsExtJsJsonTree()));