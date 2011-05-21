<?php

/**
 * ontology classes
 */
class Tht_Import_Class extends Ontologyclass
{
    protected $children   = array();
    protected $parentName = null;
    
    public function hasParent()
    {
        if($this->parentName === null){
            return false;
        }
        return true;
    }
    
    public function hasChildren()
    {
        if(count($this->children) === 0){
            return false;
        }
        return true;
    }
    
    public function setParentName($parentName)
    {
        $this->parentName = $parentName;
    }
    
    public function getParentName()
    {
        if($this->hasParent()){
            return $this->parentName;
        }
    }
    
    public function addChild($childName)
    {
        $this->children[] = $childName;
    }
    
    public function debug($level=0)
    {
        echo str_repeat(' ', $level) . $this->name . "\r\n";
        foreach($this->children as $child){
            Tht_Import_Store::getClass($child)->debug(($level + 2));
        }
    }
    
    public function saveWrapper(Ontologyclass &$parentNode)
    {
        $parentNode->refresh();
        try {
            $this->getNode()->insertAsLastChildOf($parentNode);
        } catch (Exception $e) {
            Zend_Registry::get('logger')->log($e->__toString(), 5);
        }
        foreach($this->children as $child){
            Tht_Import_Store::getClass($child)->saveWrapper($this);
        }
    }
}