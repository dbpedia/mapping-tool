<?php
/**
 * class to represent a specific MediaWiki page
 */
class Tht_MediaWiki_Document implements Tht_MediaWiki_IDocument
{
    protected $title         = null;
    protected $text          = null;
    protected $edittoken     = null;
    protected $basetimestamp = null;
    protected $namespace     = null;
    protected $pageid        = null;
    protected $lastrevid     = null;
    protected $redirects     = null;
    /**
     * creates a new MediaWiki page representation
     *
     * @param string $title title of the MediaWiki page
     * @param string $text body of the MediaWiki page
     */
    public function __construct($title=null, $text=null)
    {
        $this->title = $title;
        $this->text  = $text;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setText($text)
    {
        $this->text = $text;
    }

    public function getText()
    {
        return $this->text;
    }
    
    public function setEdittoken($edittoken)
    {
        $this->edittoken = $edittoken;
    }

    public function getEdittoken()
    {
        return $this->edittoken;
    }

    public function setBasetimestamp($basetimestamp)
    {
        $this->basetimestamp = $basetimestamp;
    }

    public function getBasetimestamp()
    {
        return $this->basetimestamp;
    }
    
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
    }
    
    public function getNamespace()
    {
        return $this->namespace;
    }
    
    public function setPageid($pageid)
    {
        $this->pageid = $pageid;
    }
    
    public function getPageid()
    {
        return $this->pageid;
    }
    
    public function setLastrevid($lastrevid)
    {
        $this->lastrevid = $lastrevid;
    }
    
    public function getLastrevid()
    {
        return $this->lastrevid;
    }

    public function setRedirects($redirects)
    {
        $this->redirects = $redirects;
    }
    
    public function getRedirects()
    {
        return $this->redirects;
    }
}