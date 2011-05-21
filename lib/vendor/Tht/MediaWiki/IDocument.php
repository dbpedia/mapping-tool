<?php
/**
 * interface for objects that present
 * a MediaWiki page
 */
interface Tht_MediaWiki_IDocument
{
    public function getTitle();
    public function getText();
    public function getEdittoken();
    public function getBasetimestamp();
    public function getNamespace();
    public function getPageid();
    public function getLastrevid();
}