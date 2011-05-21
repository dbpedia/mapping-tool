<?php

interface Tht_MediaWiki_IUser
{
    public function setCookie(Zend_Http_Cookie $cookie);
    public function getCookie();
    public function getUsername();
    public function getPassword();
}