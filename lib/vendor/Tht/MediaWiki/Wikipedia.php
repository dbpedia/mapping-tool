<?php

class Tht_MediaWiki_Wikipedia extends Tht_MediaWiki_Reader_Core
{
    public function __construct($apiUrl)
    {
        parent::__construct($apiUrl);
    }

    public function getRandomExampleUrlByTemplate($title)
    {
        $getParameters = array(
            'action'        => 'query',
            'list'          => 'embeddedin',
            'eititle'       => $title,
            'eilimit'       => Zend_Registry::get('config')->wikipedia->examples->limit,
            'eifilterredir' => 'nonredirects',
            'format'        => 'json',
            'einamespace'   => 0
        );

        $this->client->resetParameters();
        $this->client->setParameterGet($getParameters);
        $response = $this->client->request(Zend_Http_Client::GET);

        return $this->_getRandomUrlFromJsonUrlList($response->getBody());
    }

    protected function _getRandomUrlFromJsonUrlList($json)
    {
        $urlList = array();
        $response = json_decode($json, true);
        foreach($response['query']['embeddedin'] as $page){
            if(isset($page['title'])){
                $urlList[] = $page['title'];
            }
        }

        $wiki_url = Zend_Registry::get('config')->wikipedia->wiki->url;

        $output   = array();
        $max_rand = count($urlList)-1;
        for($i=0; $i < 5; $i++){
            $random   = mt_rand(0, $max_rand);
            $output[] = array(
                'url'  => $wiki_url . '/' . $urlList[$random],
                'name' => htmlentities($urlList[$random])
            );
        }

        return json_encode( $output );
    }

    public function getSuggestedPagesByTitle($title)
    {
        $getParameters = array(
            'action'    => 'opensearch',
            'limit'     => Zend_Registry::get('config')->wikipedia->autocomplete->limit,
            'format'    => 'json',
            'search'    => $title,
            'namespace' => Zend_Registry::get('config')->wikipedia->ns->templates
        );

        $this->client->resetParameters();
        $this->client->setParameterGet($getParameters);
        $response = $this->client->request(Zend_Http_Client::GET);

        return $this->_getSuggestedPagesFromJsonPageList($response->getBody());
    }

    protected function _getSuggestedPagesFromJsonPageList($json)
    {
        $json = json_decode($json, true);

        if(!isset($json[1])){
            return json_encode(array());
        }

        $out = array();

        foreach($json[1] as $site){
            $out[] = array('site' => str_replace('Template:', '', $site));
        }

        return json_encode(
            array(
                'total' => count($out),
                'data' => $out
            )
        );
    }
    
    public function getMarkupByTitle($title)
    {
        $response = parent::getMarkupByTitle($title);
        return json_encode(array(
            'templateMarkup' => $response->getText()
        ));
    }
}