<?php

class Tht_Dml_Property implements Tht_Dml_IExtJsJsonTree
{
    public $name  = '';
    public $type  = '';
    public $multiplicity = array('min' => 1, 'max' => 1);
    public $documentation = '';

    public $value = array();

    public function __construct($name, $type, $multiplicity = null, $documentation = null)
    {
        $this->name  = $name;
        $this->type  = $type;
        
        if($documentation !== null){
            $this->documentation = $documentation;
        }
        
        switch($multiplicity){
            case '*':
                $this->multiplicity = array('min' => 0, 'max' => null);
                break;
            case '+':
                $this->multiplicity = array('min' => 1, 'max' => null);
                break;
            case '?':
                $this->multiplicity = array('min' => 0, 'max' => 1);
                break;
            default:
                $this->multiplicity = array('min' => 1, 'max' => 1);
        }
    }

    public function getMultiplicity()
    {
        return $this->multiplicity;
    }
    
    public function getType(){
        return $this->type();
    }

    public function getName(){
        return $this->name;
    }

    public function getAsExtJsJsonTree()
    {
        $out = array();

        $out['text']     = $this->name;
        $out['label']    = $this->name;
        $out['type']     = $this->type;
        $out['isTemplate'] = false;
        $out['nodeType'] = PREFIX . $this->type;
        foreach($this->value as $node){
            if(is_array($node)){
                foreach($node as $child){
                    $out['children'][] = $child->getAsExtJsJsonTree();
                }
            } else {
                if(get_class($node) === 'Tht_Dml_Scalar'){
                    $out['text']     = $this->name . ': ' . $node->data;
                    $out['value']    = $node->data;
                    $out['type']     = $this->type;
                    $out['nodeType'] = PREFIX . $this->type;
                    $out['leaf']     = true;
                    continue;
                }
                
                unset($out['leaf']);
                $out['nodeType'] = PREFIX . $this->type;
                $out['children'][] = $node->getAsExtJsJsonTree();
            }
        }
        return $out;
    }
}