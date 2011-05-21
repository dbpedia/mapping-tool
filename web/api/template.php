<?php

if(!defined('__INCLUDE_LOADED__')){
    die('please include "include.php" for functionality');
}
// load grammar
Tht_Dml_Grammar::loadGrammarFile(__ROOT__ . '/grammar/dbpedia_mapping_grammar.xml');

// fetch templates from grammar
$grammar_templates = Tht_Dml_Grammar::getTemplateList();

// sort templates alphabetical
sort($grammar_templates);

// initiate root element of nodes
$templates = array();

// iterate on defined templates
foreach ($grammar_templates as $templateName){
    
    // fetch template object by template name from grammar
    $template = Tht_Dml_Grammar::getTemplateByName($templateName);
    
    // initiate node
    $tmp = array();
    $tmp['text']       = $template->name;
    $tmp['label']      = $template->name;
    $tmp['name']       = $template->name;
    $tmp['iconCls']    = 'my-tree-icon-' . PREFIX . $template->type;
    $tmp['type']       = $template->type;
    $tmp['nodeType']   = PREFIX . $template->type;
    $tmp['isTemplate'] = true;
    
    // fetch properties of specific template
    $properties = $template->getPropertyList();
    
    // sort properties alphabetical
    sort($properties);
    
    // iterate on mandatory properties
    foreach($properties as $propertyName){
        $property = $template->getPropertyByName($propertyName);
        
        $minMultiplicity = $property->multiplicity['min'];
        
        $child = array(
            'text'       => ($minMultiplicity == 0 ? '<i>' . $property->name . '</i>' : $property->name),
            'label'      => $property->name,
            'deletable'  => ($minMultiplicity == 0 ? true : false),
            'iconCls'    => 'my-tree-icon-' . PREFIX . $property->type,
            'type'       => $property->type,
            'nodeType'   => PREFIX . $property->type,
            'isTemplate' => false,
            'leaf'       => true
        );
        if($property->documentation !== ''){
            $child['qtip'] = $property->documentation;
        }
        $tmp['children'][] = $child;
    }
    
    // if template has no children declare as leaf
    if(!isset($tmp['children'])){
        $tmp['leaf']     = true;
    } else {
        $tmp['nodeType'] = PREFIX . $template->type . "Async";
    }
    
    // add template to templates
    $templates[] = $tmp;
}

Tht_Helper_Header::cacheJS(array(
    __ROOT__ . '/grammar/dbpedia_mapping_grammar.xml',
    __FILE__
));
echo json_encode($templates);