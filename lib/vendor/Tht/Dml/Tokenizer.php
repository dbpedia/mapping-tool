<?php

// default control characters for mapping language
if( !defined('DBPEDIA_TOKEN_TEMPLATE_START') ){
    define('DBPEDIA_TOKEN_TEMPLATE_START', '{{');
}
if( !defined('DBPEDIA_TOKEN_TEMPLATE_END') ){
    define('DBPEDIA_TOKEN_TEMPLATE_END', '}}');
}
if( !defined('DBPEDIA_TOKEN_PROPERTY_START') ){
    define('DBPEDIA_TOKEN_PROPERTY_START', '|');
}
if( !defined('DBPEDIA_TOKEN_PROPERTY_EQUAL') ){
    define('DBPEDIA_TOKEN_PROPERTY_EQUAL', '=');
}
if( !defined('DBPEDIA_TOKEN_COMMENT_START') ){
    define('DBPEDIA_TOKEN_COMMENT_START', '<!--');
}
if( !defined('DBPEDIA_TOKEN_COMMENT_END') ){
    define('DBPEDIA_TOKEN_COMMENT_END', '-->');
}

if( !defined('PREFIX') ){
    define('PREFIX', 'DBpedia');
}

function pregQuoteMapperCb($value)
{
    return preg_quote($value);
}

class Tht_Dml_Tokenizer
{
    protected static $index  = 0;       // current cursor position
    protected static $tokens = array(); // token store
    protected static $length = 0;       // number of tokens

    /**
     * saves a given array as token list,
     * sets the $length of the token list
     * and and sets the internal cursor to
     * start position
     *
     * @param array $tokens
     */
    public static function setTokens(array $tokens)
    {
        self::$tokens = self::stripEmtpyFromArray($tokens);
        self::$length = count(self::$tokens);
        self::reset();
    }

    /**
     * shows if list is empty or not
     * @return bool
     */
    public static function isEmpty()
    {
        if(self::$length == self::$index){
            return true;
        }
        return false;
    }

    /**
     * returns the current list element
     *
     * @return string
     */
    public static function current()
    {
        return isset(self::$tokens[self::$index]) ? self::$tokens[self::$index] : false;
    }

    /* strip comments from markup
     * without losing them
     * but with ability to validate rest
     */
    public static function walkWithUnset()
    {
        $tmp = self::current();
        if($tmp !== false){
            unset(self::$tokens[self::$index]);
            self::$tokens = array_values(self::$tokens);
            self::$length = count(self::$tokens);
        }
        
        return $tmp;
    }

   /**
    * returns the element which is $steps before
    * current position
    *
    * @param int $steps
    * @return string
    */
    public static function back($steps=1)
    {
        self::$index = self::$index - $steps;
        return self::current();
    }

    /**
     * returns the current element and steps
     * $steps forward
     *
     * @param int $steps
     * @return string
     */
    public static function walk($steps=1)
    {
        self::$index = self::$index + $steps;
        return isset(self::$tokens[(self::$index - $steps)]) ? self::$tokens[(self::$index - $steps)] : false;
    }

    /**
     * sets internal cursor to position $index
     * 
     * @param int $index
     */
    public static function reset($index=0)
    {
        self::$index = $index;
    }

    /**
     * returns element $steps from current position
     * 
     * @param int $steps
     * @return string
     */
    public static function show($steps=0)
    {
        return isset(self::$tokens[(self::$index + $steps)]) ? self::$tokens[(self::$index + $steps)] : false;
    }

    /**
     * returns string representation of token list
     *
     * @return string
     */
    public static function debug()
    {
        return print_r(self::$tokens, true);
    }

    /**
     * clear token list
     */
    public static function clear()
    {
        self::$tokens = array();
        self::$index  = 0;
        self::$length = 0;
    }

    /**
     * returns number of elements in list
     * 
     * @return int
     */
    public static function length()
    {
        return self::$length;
    }

    /**
     * helper function to trim() each array element
     * and strip empty ones afterwards
     * 
     * @param array $array
     * @return array
     */
    public static function stripEmtpyFromArray(array $array)
    {
        $tmp = array();
        foreach($array as $element){
            $element = trim($element);
            if($element == ''){ // strip empty tokens
                continue;
            }
            $tmp[] = $element;
        }
        return $tmp;
    }
    
    /**
     * fill token list from a given mapping
     * language string
     * 
     * @param string $markup
     */
    public static function parseTokensFromMarkup($markup)
    {
        // TODO PHP5.3 use of lambda function
        //$pregQuoteMapperCb = function($value){
        //    return preg_quote($value);
        //};

        // change "-->" to " --> "
        $markup = str_replace(DBPEDIA_TOKEN_COMMENT_END, ' ' . DBPEDIA_TOKEN_COMMENT_END . ' ', $markup);

        // change "<!--" to " <!-- "
        $markup = str_replace(DBPEDIA_TOKEN_COMMENT_START, ' ' . DBPEDIA_TOKEN_COMMENT_START . ' ', $markup);

        // change "{{{{" to "{{ {{"
        // change "}}}}" to "}} }}"
        $tokens = array(
            DBPEDIA_TOKEN_TEMPLATE_START,
            DBPEDIA_TOKEN_TEMPLATE_END
        );
        $tokens = array_map('pregQuoteMapperCb', $tokens); // quote each token for regex
        $markup = preg_replace('~(' .  implode('|', $tokens) . ')~', '$1 ', $markup); // execute regex

        // tokenize markup
        $tokens = array(
            DBPEDIA_TOKEN_TEMPLATE_START,
            DBPEDIA_TOKEN_TEMPLATE_END,
            DBPEDIA_TOKEN_PROPERTY_START,
            DBPEDIA_TOKEN_PROPERTY_EQUAL,
            DBPEDIA_TOKEN_COMMENT_START,
            DBPEDIA_TOKEN_COMMENT_END
        );
        $tokens = array_map('pregQuoteMapperCb', $tokens); // quote each token for regex
        $tokenizedMarkup = preg_split('~(' . implode('|', $tokens) . ')+~', $markup, -1, PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY); // execute regex

        // set tokens
        self::setTokens($tokenizedMarkup);
    }
}