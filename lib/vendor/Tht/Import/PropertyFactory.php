<?php

class Tht_Import_PropertyFactory
{
    protected static $type  = null; // Objectproperty or Datatypeproperty
    
    public static function createPropertyFromWikiMarkup(Tht_MediaWiki_Document $document)
    {
        // parse template data of the MediaWiki page
        $data = self::readTemplate($document->getText());

        // check if parsing was successful
        if(!($data instanceof Tht_Dml_Template)){
            return;
        }

        // valid classes are
        // Objectproperty and Datatypeproperty
        // they reflect the templates
        // {{ObjectProperty.. and {{DatatypeProperty..
        if(!class_exists(self::$type)){
            return;
        }

        // instantiate an Objectproperty or
        // Datatypeproperty object
        $property = new self::$type;

        // set data
        $property->name  = self::lcfirst(str_replace(array(' ','OntologyProperty:'), array('_',''), $document->getTitle()));
        $property->label = self::getLabel($data);
        
        if(self::hasRange($data)){
            $property->rangename = self::getRange($data);
        }

        // try to save the property in the database
        try{
            $property->save();
        } catch (Exception $e){
            Zend_Registry::get('logger')->log($e->__toString(), 5);
        }

        // fetch the domain (ontologyclass) of the property from database
        $class = $class = Doctrine::getTable('Ontologyclass')->findOneByName(self::getDomain($data));

        // check if domain was found and if so connect domain
        // and property in the database
        if($class instanceof Ontologyclass){
            $relationORMClassName    = "Ontologyclass" . self::$type;
            $relationORMPropertyName = strtolower(self::$type) . '_id';

            // build and save table relation
            $classProperty = new $relationORMClassName;
            $classProperty->ontologyclass_id           = $class['id'];
            $classProperty->{$relationORMPropertyName} = $property['id'];

            try{
                $classProperty->save();
            } catch(Exception $e){
                Zend_Registry::get('logger')->log($e->__toString(), 5);
            }
        }

        // reset the type of the property
        self::$type = null;
    }
    
    public static function readTemplate($rawTemplate)
    {
        $rawTemplate = trim(preg_replace('~{{\s*DisclaimerOntologyProperty\s*}}~', '', $rawTemplate));
    
        try{
            $property = Tht_Dml_Parser::parse($rawTemplate, __ROOT__ . '/grammar/dbpedia_elements_grammar.xml');
            $tmp = $property[0][0];
            self::$type = ucfirst(strtolower(trim($tmp->name)));
        } catch (Exception $e){
            Zend_Registry::get('logger')->log($e->__toString(), 5);
            return false;
        }
        
        return $property[0][0];
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
    
    public static function hasDomain(Tht_Dml_Template $data)
    {
        return $data->getNodeValueByNode('rdfs:domain');
    }
    
    public static function getDomain(Tht_Dml_Template $data)
    {
        if(!self::hasDomain($data)){
            return 'owl:Thing';
        }
        
        return trim($data->getNodeValueByNode('rdfs:domain'));
    }
    
    public static function hasRange(Tht_Dml_Template $data)
    {
        return $data->getNodeValueByNode('rdfs:range');
    }
    
    public static function getRange(Tht_Dml_Template $data)
    {
        if(self::hasRange($data)){
            return trim($data->getNodeValueByNode('rdfs:range'));
        }
    }
    
    // sytem lcfirst needs php >=5.3
    public static function lcfirst($string)
    {
        return strtolower(substr($string, 0, 1)) . substr($string, 1, strlen($string));
    }
}