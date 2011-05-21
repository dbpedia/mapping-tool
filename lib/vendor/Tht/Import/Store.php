<?php

/**
 * custom store for ontology classes
 * with the ability to represent the
 * hierarchical ontology tree
 */
class Tht_Import_Store
{
    protected static $classes = array(); // list of ontology classes
    protected static $count     = 0;     // counter for ontology classes

    /**
     * adds an ontology class object to the store
     *
     * @param oClass $class ontology class object
     */
    public static function addClass(Tht_Import_Class $class)
    {
        self::$classes[($class->name)] = $class;
        self::$count++;
    }

    /**
     * defines an ontology class object as a child object of
     * a parent ontology class object
     *
     * @param string $parentName parent ontology class name
     * @param string $childName child ontology class name
     */
    public static function addChildToClass($parentName, $childName)
    {
        if(isset(self::$classes[$parentName])){
            self::$classes[$parentName]->addChild($childName);
            return true;
        }
        Zend_Registry::get('logger')->log(__FILE__ . ' ' . __LINE__ . ' unknown parent class ' . $parentName, 5);
        //throw new Exception('unknown parent class ' . $parentName);
    }

    /**
     * defines an ontology class object as a parent of a
     * child ontology class
     *
     * @param string $childName child ontology class name
     * @param string $parentName parent ontology class name
     */
    public static function addParentToClass($childName, $parentName)
    {
        if(isset(self::$classes[$childName])){
            self::$classes[$childName]->setParentName($parentName);
            return true;
        }
        Zend_Registry::get('logger')->log(__FILE__ . ' ' . __LINE__ . ' unknown child class ' . $childName, 5);
        //throw new Exception('unknown child class ' . $childName);
    }

    /**
     * returns an ontology class object by
     * the given ontology name
     *
     * @param string $className requested ontology class name
     * @return object
     */
    public static function getClass($className)
    {
        if(isset(self::$classes[$className])){
            return self::$classes[$className];
        }
        Zend_Registry::get('logger')->log(__FILE__ . ' ' . __LINE__ . ' unknown class name ' . $className, 5);
        //throw new Exception('unknown class ' . $className);
    }

    /**
     * prints a graphical representation of the class
     * tree in the store to screen
     */
    public static function debug()
    {
        // print the number of classes
        echo self::$count . " classes:\r\n\r\n";

        // iterate over all classes
        foreach(self::$classes as $class){
            // start with the super classes, which
            // have no parents
            if(!$class->hasParent()){
                $class->debug();
            }
        }
    }

    /**
     * starts the saving process of the tree through
     * a recursive implementation in the ORM classes
     * as a branch of the given root node
     *
     * @param oClass $rootNode root node -> owl:Thing
     */
    public static function save(Ontologyclass &$rootNode)
    {
        foreach(self::$classes as $class){
            if(!$class->hasParent()){
                $class->saveWrapper($rootNode);
            }
        }
    }
}
