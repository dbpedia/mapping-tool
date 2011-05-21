<?php

class Tht_Import_DatatypeFactory
{
    protected static $type  = null;
    
    public static function createDatatypeFromWikiMarkup(Tht_MediaWiki_Document $document)
    {
      
        //$data = self::readTemplate($document->getText());
        
        $datatype        = new Datatype();
        $datatype->name  = self::lcfirst(str_replace(array(' ', 'Datatype:'), array('_',''), $document->getTitle()));
        $datatype->label = str_replace('Datatype:', '', $document->getTitle());
        
        try{
            $datatype->save();
            //echo '.';
        } catch(Exception $e){
            Zend_Registry::get('logger')->log($e->__toString(), 5);
        }
    }
    
    /*
    public static function readTemplate($rawTemplate)
    {
        $rawTemplate = trim(preg_replace('~{{\s*DisclaimerDatatype\s*}}~', '', $rawTemplate));
    
        try{
            $datatype = Tht_Dml_Parser::parse($rawTemplate, __ROOT__ . '/grammar/dbpedia_elements_grammar.xml');
        } catch (Exception $e){
            Zend_Registry::get('logger')->log($e->__toString(), 5);
        }
        return $datatype[0][0];
    }
    */

    // lcfirst needs php >=5.3
    public static function lcfirst($string)
    {
        return strtolower(substr($string, 0, 1)) . substr($string, 1, strlen($string));
    }
}