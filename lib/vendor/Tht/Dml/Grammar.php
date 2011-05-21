<?php

class Tht_Dml_Grammar
{
    // local grammar store as array
    protected static $grammar = '';

    // local template object store
    protected static $templates = array();

    // to store templates in lower case
    // deliver the right object even GeoCooRDInates
    // is mapped instead of GeoCoordinates
    protected static $templatesInLowerCase = array();

    // local type store
    protected static $types = array();

    /**
     * reads the grammar from a given path
     * of a grammar xml file
     *
     * @param string $xmlFile
     */
    public static function loadGrammarFile($xmlFile)
    {
        // prevent double loading
        if(self::$grammar !== ''){
            return;
        }

        // simple conversion from XML to array
        // 1. read xml
        // 2. encode to json
        // 3. decode from json
        // 4. save array to local grammar store
        self::$grammar = json_decode(json_encode(simplexml_load_file($xmlFile)), true);

        // iterate over templates
        // 1. create template objects
        // 2. add them to the local template store
        foreach(self::$grammar['template'] as $template){
            self::_addTemplate($template);
        }
    }

    /**
     * internal function to create a
     * Tht_Dml_Template object by a given
     * array representation of grammar xml
     *
     * @param array $template
     */
    protected static function _addTemplate(array $template)
    {
        $templateName = $template['@attributes']['name'];
        $templateType = $template['@attributes']['type'];

        self::$types[$templateType] = true;

        // create Tht_Dml_Template object
        $newTemplate = new Tht_Dml_Template($templateName, $templateType);

        // iterate on properties an add them to the Tht_Dml_Template object
        foreach($template['property'] as $property){
            $propertyName = $property['@attributes']['name'];
            $propertyType = $property['@attributes']['type'];

            self::$types[$propertyType] = true;

            $propertyMultiplicity = isset($property['@attributes']['multiplicity']) ? $property['@attributes']['multiplicity'] : null;

            $documentation = null;
            if(isset($property['documentation'])){
                $documentation = $property['documentation'];
            }
            
            // create corresponding Tht_Dml_Property object
            $newProperty = new Tht_Dml_Property($propertyName, $propertyType, $propertyMultiplicity, $documentation);
            $newTemplate->addProperty($newProperty);
        }

        // add Tht_Dml_Template object to internal store
        self::$templates[$templateName] = true;
        
        // lcfirst() needs php >5.3
        $templateName = self::lcfirst($templateName);
        self::$templatesInLowerCase[$templateName] = $newTemplate;
    }

    // lcfirst needs php >=5.3
    public static function lcfirst($string)
    {
        return strtolower(substr($string, 0, 1)) . substr($string, 1, strlen($string));
    }
    
    /**
     * returns presentation of grammar array
     *
     * @return string
     */
    public static function debug()
    {
        return print_r(self::$templates, true);
    }

    /**
     * returns a Tht_Dml_Template object
     * of a given name
     *
     * @param string $templateName
     * @return Tht_Dml_Template
     */
    public static function getTemplateByName($templateName)
    {
        $templateName = self::lcfirst($templateName);
        if(isset(self::$templatesInLowerCase[$templateName])){
            return clone self::$templatesInLowerCase[$templateName];
        }
        throw new Exception('unknown template ' . $templateName);
    }

    /**
     * determine if given a template name is
     * defined in grammar xml
     *
     * @param string $templateName
     * @return bool
     */
    public static function isSupportedTemplate($templateName)
    {
        $templateName = self::lcfirst($templateName);
        return isset(self::$templatesInLowerCase[$templateName]);
    }

    /**
     * returns an array of all available templates
     * defined in grammar xml file
     *
     * @return array
     */
    public static function getTemplateList()
    {
        return array_keys(self::$templates);
    }

    /**
     * returns an array of all availabe types
     * defined in grammar xml file
     *
     * @return array
     */
    public static function getTypeList()
    {
        return array_keys(self::$types);
    }

}
