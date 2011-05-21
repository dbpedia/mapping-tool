<?php
/**
 * class to represent a list of MediaWiki pages
 * implements ArrayAccess, Iterator and Countable interface
 * to simulate array behavior
 */
class Tht_MediaWiki_DocumentList implements ArrayAccess, Iterator, Countable
{
    // array of objects, which implement the
    // Tht_MediaWiki_IDocument interface
    protected $documents = array();

    public function __construct()
    {
        // nothing
    }

    public function offsetSet($offset, $value)
    {
        if(!($value instanceof Tht_MediaWiki_Document)){
            throw new Exception('given value is of wrong type - expected Tht_MediaWiki_Document');
        }

        if($offset === null){
            $this->documents[] = $value;
        } else {
            $this->documents[$offset] = $value;
        }
    }
    
    public function offsetExists($offset)
    {
        return isset($this->documents[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->documents[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->documents[$offset]) ? $this->documents[$offset] : null;
    }

    public function rewind()
    {
        reset($this->documents);
    }

    public function current()
    {
        return current($this->documents);
    }

    public function key()
    {
        return key($this->documents);
    }

    public function next()
    {
        return next($this->documents);
    }

    public function valid()
    {
        return $this->current() !== false;
    }

    public function count()
    {
       return count($this->documents);
    }
}