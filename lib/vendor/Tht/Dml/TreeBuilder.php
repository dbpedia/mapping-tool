<?php

class Tht_Dml_TreeBuilder implements Tht_Dml_IExtJSJsonTree
{
    protected $tree;
    protected $rootName = null;

    public function __construct(array $tree, $rootName=null)
    {
        $this->tree = $tree;
        $this->rootName = $rootName;
    }
    
    public function getAsExtJsJsonTree()
    {
        $out = array();
        $out['text']      = is_null($this->rootName) ? 'Mapping' : $this->rootName;
        $out['type']      = 'TemplateMapping';
        $out['deletable'] = false;
        //$out['nodeType'] = PREFIX . 'TemplateMapping';
        foreach($this->tree[0] as $node){
            $out['children'][] = $node->getAsExtJsJsonTree();
        }

        return $out;
    }
}