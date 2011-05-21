<?php

class Tht_Dml_Template implements Tht_Dml_IExtJsJsonTree
{
    public    $name = '';
    public    $type = '';
    protected $properties = array();
    
    public $nodes = array();
    
    public function __construct($name, $type){
        $this->name = $name;
        $this->type = $type;
    }
    
    public function getNodes(){
        return $this->nodes;
    }
    
    public function getNodeValueByNode($name){
        foreach($this->nodes as $node){
            if($node->name === $name){
                if(isset($node->value[0])){
                    return $node->value[0]->data;
                }
            }
        }
        return false;
    }
    
    public function addProperty(Tht_Dml_Property $property){
        $this->properties[($property->getName())] = $property;
    }
    
    public function getPropertyByName($propertyName){
        return clone $this->properties[$propertyName];
    }

    public function getPropertyList(){
        return array_keys($this->properties);
    }
    
    public function createProperty()
    {
        $property = null;
        
        while(!Tht_Dml_Tokenizer::isEmpty()){
            $chunk = Tht_Dml_Tokenizer::walk();
        
            // fetch comments:
            // | <comments> property
            $comments = array();
            if($chunk === DBPEDIA_TOKEN_COMMENT_START){
                $comments[] = new Tht_Dml_Comment();
                continue;
            }
        
            // create property
            if($this->isValidProperty($chunk)){
                $property = self::getPropertyByName($chunk);
                
                // add | <comments> property
                // as child nodes of property
                //if(count($comments) > 0){
                    //foreach($comments as $comment){
                    //    $property->value[] = $comment;
                    //}
                //}
                
                while(!Tht_Dml_Tokenizer::isEmpty()){
                    $tmpChunk = Tht_Dml_Tokenizer::walk();
                
                    // fetch comments:
                    // | property <comments> =
                    if($tmpChunk === DBPEDIA_TOKEN_COMMENT_START){
                        //$property->value[] = new Tht_Dml_Comment();
                        $comments[] = new Tht_Dml_Comment();
                        continue;
                    }
                
                    // stop fetching when equal sign appears
                    if($tmpChunk === DBPEDIA_TOKEN_PROPERTY_EQUAL){
                        break;
                    }
                
                    // if token is neither a comment nor an equal sign
                    // it's a non valid token
                    if(!in_array(Tht_Dml_Tokenizer::current(), array(DBPEDIA_TOKEN_COMMENT_START, DBPEDIA_TOKEN_PROPERTY_EQUAL))){
                        throw new Exception('Missing equal sign for property value ' . $chunk);
                    }
                }
                
                while(!Tht_Dml_Tokenizer::isEmpty()){
                    $propertyValue = Tht_Dml_Tokenizer::walk();
    
                    if($propertyValue === DBPEDIA_TOKEN_COMMENT_START){
                        // if Comments as values should be ignored try
                        // sth similar to this:
                        // Tht_Dml_Tokenizer::appendCurrent(new Tht_Dml_Comment)
                        //$property->value[] = new Tht_Dml_Comment();
                        $comments[] = new Tht_Dml_Comment();
                        continue;
                    }
    
                    if($propertyValue === DBPEDIA_TOKEN_PROPERTY_START){
                        Tht_Dml_Tokenizer::back();
                        return array(
                            'comments' => $comments,
                            'property' => $property
                        );
                    }
    
                    if($propertyValue === DBPEDIA_TOKEN_TEMPLATE_END){
                        // fetch comment
                        // .. <comment> }}
                        if(Tht_Dml_Tokenizer::current() === DBPEDIA_TOKEN_COMMENT_START){
                            //$property->value[] = new Tht_Dml_Comment();
                            $comments[] = new Tht_Dml_Comment();
                            Tht_Dml_Tokenizer::back();
                            continue;
                        }
                        Tht_Dml_Tokenizer::back();
                        return array(
                            'comments' => $comments,
                            'property' => $property
                        );
                    }
    
                    if( $propertyValue === DBPEDIA_TOKEN_TEMPLATE_START ){
                        Tht_Dml_Tokenizer::back();
                        $property->value[] = Tht_Dml_Parser::createTemplate();
                    }
    
                    if( $propertyValue !== DBPEDIA_TOKEN_TEMPLATE_START && Tht_Dml_Tokenizer::show(-2) === DBPEDIA_TOKEN_PROPERTY_EQUAL){
                        $property->value[] = new Tht_Dml_Scalar($propertyValue);
                    }
    
                }
            } else {
              throw new Exception('Mapping not valid - unsupported property ' . $this->name . '::' . $chunk);
            }
        }
        throw new Exception('mapping not valid structured - missing ' . DBPEDIA_TOKEN_TEMPLATE_END);
    }
    
    public function isValidProperty($propertyname)
    {
        if(isset($this->properties[$propertyname])){
            return true;
        }
        return false;
    }
    
    public function parse()
    {
        $result = array();
        $comments = array();
    
        while(!Tht_Dml_Tokenizer::isEmpty()){
            $chunk = Tht_Dml_Tokenizer::walk();
    
            if($chunk === DBPEDIA_TOKEN_COMMENT_START){
                //$this->nodes[] = new Tht_Dml_Comment();
                $comments[] = new Tht_Dml_Comment();
                continue;
            }

            // fetch comments
            // | <comment> property = ..
            if($chunk === DBPEDIA_TOKEN_PROPERTY_START){
                if(Tht_Dml_Tokenizer::current() === DBPEDIA_TOKEN_COMMENT_START){
                    //$this->nodes[] = new Tht_Dml_Comment();
                    $comments[] = new Tht_Dml_Comment();
                    Tht_Dml_Tokenizer::back();
                    continue;
                }
                $tmp = $this->createProperty();
                $this->nodes[] = $tmp['property'];
                if(isset($tmp['comments'])){
                    $comments = array_merge($comments, $tmp['comments']);
                }
            }
    
            if($chunk === DBPEDIA_TOKEN_TEMPLATE_START){
                throw new Exception('Mapping not valid - nesting exception ' . $this->name . '::' . $chunk);
            }
    
            if($chunk === DBPEDIA_TOKEN_TEMPLATE_END){
                return array(
                    'template' => $this,
                    'comments' => $comments
                );
            }
        }
    }

    public function getAsExtJsJsonTree()
    {
        $out = array();

        $out['text']     = $this->name;
        $out['type']     = $this->type;
        $out['nodeType'] = PREFIX . $this->type;
        $out['label']    = $this->name;
        $out['name']     = $this->name;
        $out['expanded'] = true;
        $out['isTemplate'] = true;
        foreach($this->nodes as $node){
            $out['children'][] = $node->getAsExtJsJsonTree();
        }
        
        if(!isset($out['children']) || count($out['children']) == 0){
            $out['nodeType'] = PREFIX . $this->type;
            $out['leaf'] = true;
        }
        
        return $out;
    }
}