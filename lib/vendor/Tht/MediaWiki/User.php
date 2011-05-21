<?php

class Tht_MediaWiki_User implements Tht_MediaWiki_IUser
{
    protected $username = null;
    protected $password = null;
    protected $cookie   = null;
    
    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }
    
    public function setCookie(Zend_Http_Cookie $cookie)
    {
        $this->cookie = $cookie;
    }
    
    public function getCookie()
    {
        return $this->cookie;
    }
    
    public function getUsername()
    {
        return $this->username;
    }
    
    public function getPassword()
    {
        return $this->password;
    }
}