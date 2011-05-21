<?php

class Tht_Import_ClassFactory
{
    protected static $type = null;
    
    public static function createClassFromWikiMarkup(Tht_MediaWiki_Document $document)
    {
        $template = self::readTemplate($document->getText());

        if(!($template instanceof Tht_Dml_Template)){
            return;
        }
    
        $class        = new Tht_Import_Class();
        $class->name  = str_replace(array(' ','OntologyClass:'), array('_',''), $document->getTitle());
        $class->label = self::getLabel($template);
    
        if(self::hasParent($template)){
            $class->setParentName(self::getParentName($template));
        }
        
        return $class;
    }
    
    public static function readTemplate($rawTemplate)
    {
        $rawTemplate = trim(preg_replace('~{{\s*DisclaimerOntologyClass\s*}}~', '', $rawTemplate));
    
        try{
            $class = Tht_Dml_Parser::parse($rawTemplate, __ROOT__ . '/grammar/dbpedia_elements_grammar.xml');
        } catch (Exception $e){
            Zend_Registry::get('logger')->log($e->__toString(), 5);
            return false;
        }
        return $class[0][0];
    }
    
    public static function hasParent(Tht_Dml_Template $data)
    {
        if($data->getNodeValueByNode('rdfs:subClassOf')){
            return true;
        }
        return false;
    }
    
    public static function getParentName(Tht_Dml_Template $data)
    {
        if(self::hasParent($data)){
          return trim($data->getNodeValueByNode('rdfs:subClassOf'));
        }
        return false;
    }
    
    public static function getLabel(Tht_Dml_Template $data)
    {
        if(($label = $data->getNodeValueByNode('rdfs:label@en'))){
            return trim($label);
        } else if(($label = $data->getNodeValueByNode('rdfs:label'))){
            return trim($label);
        }
        
        return 'unknown';
    }
}