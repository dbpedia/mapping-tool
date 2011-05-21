<?php

class Tht_Dml_Comment implements Tht_Dml_IExtJsJsonTree
{
    public    $value = '';
    protected $type  = 'Comment';
    public    $name  = 'Comment';

    public function __construct($test = null)
    {
        /**
         * implemented to test the class
         */
        if($test !== null){
            $this->value = $test;
            return $this;
        }

        // strip also already passed opening comment tokens
        if(Tht_Dml_Tokenizer::show(-1) === DBPEDIA_TOKEN_COMMENT_START){
            Tht_Dml_Tokenizer::back();
        }

        // strip comment tokens from token list
        // but add comment content to a Tht_Dml_Comment object
        while(($token = Tht_Dml_Tokenizer::walkWithUnset())){

            // ignore comment opening token
            if($token === DBPEDIA_TOKEN_COMMENT_START){
                continue;
            }

            // return Tht_Dml_Comment object, if token
            // indicates end of comment
            if($token === DBPEDIA_TOKEN_COMMENT_END){
                $this->value = trim($this->value);
                return $this;
            }

            // if the given token is neither a comment
            // opening token nor a closing token
            // add the current token
            $this->value .= ' ' . $token;
        }

        // if no comment ending token is found
        // throw this error
        // TODO Exception can not be thrown in __constructor
        // throw new Exception('unclosed comment found');
    }

    public function getAsExtJsJsonTree()
    {
        return array(
            'text'     => '<i>' . $this->name . ': ' . substr($this->value, 0, 20) . '...</i>',
            'label'    => $this->name,
            'nodeType' => PREFIX . $this->type,
            'value'    => $this->value,
            //'hidden'   => true,
            'leaf'     => true
        );
    }
}