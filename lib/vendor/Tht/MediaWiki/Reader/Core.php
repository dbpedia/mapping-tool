<?php

/**
 * class to read content of wiki pages
 * of a given namespace with the help
 * of the MediaWiki API
 */
abstract class Tht_MediaWiki_Reader_Core
{
    protected $format   = 'json'; // MediaWiki format
    protected $nslimit  = 500;    // namespace limit of api
    //protected $pidlimit = 50;     // amount of simultanesly requested pids

    protected $user     = null;   // Tht_MediaWiki_User object
    protected $token    = null;
    protected $client   = null;
    protected $apiUrl   = null;


    public function __construct($apiUrl)
    {
        $this->apiUrl = $apiUrl;

        $this->client = new Zend_Http_Client($this->apiUrl, array(
            'maxredirects' => 0,
            'timeout'      => 30,
            'adapter'      => 'Zend_Http_Client_Adapter_Socket',
            'useragent'    => Zend_Registry::get('config')->mediawiki->simulated->useragent,
            'keepalive'    => true
        ));
    }


    public function login(Tht_MediaWiki_IUser $user)
    {
        $this->user = $user;

        // fetch login token and set cookie
        $postParams = array(
            'action'     => 'login',
            'lgname'     => $this->user->getUsername(),
            'lgpassword' => $this->user->getPassword(),
            'format'     => 'json'
        );
        $this->client->resetParameters();
        $this->client->setParameterPost( $postParams );
        $response = $this->client->request(Zend_Http_Client::POST);

        //Zend_Registry::get('logger')->log($response, 5);
        
        // fetch login token from body
        $parsed_json = json_decode($response->getBody(), true);
        $login_token = $parsed_json['login']['lgtoken'];

        // create and set cookie to http client
        // @TODO improvement: store cookie 15min in cache?
        $parsed_url   = parse_url($this->apiUrl);
        $domain       = $parsed_url['host'];
        $headers      = $response->getHeaders();
        //Zend_Registry::get('logger')->log(print_r($headers, true), 5);
        $cookie       = Zend_Http_Cookie::fromString($headers['Set-cookie'][0] . ';domain=' . $domain);
        $this->user->setCookie($cookie);
        $this->client->setCookie($this->user->getCookie());

        // proceed login with required token and cookie
        $postParams = array(
            'action'     => 'login',
            'lgname'     => $this->user->getUsername(),
            'lgpassword' => $this->user->getPassword(),
            'lgtoken'    => $login_token,
            'format'     => 'json'
        );
        $this->client->setParameterPost( $postParams );
        $response = $this->client->request(Zend_Http_Client::POST);
        //Zend_Registry::get('logger')->log(print_r($response, true), 5);
    }

    public function isValidLogin(Tht_MediaWiki_IUser $user)
    {
        $this->user = $user;

        // fetch login token and set cookie
        $postParams = array(
            'action'     => 'login',
            'lgname'     => $this->user->getUsername(),
            'lgpassword' => $this->user->getPassword(),
            'format'     => 'json'
        );
        $this->client->resetParameters();
        $this->client->setParameterPost( $postParams );
        $response = $this->client->request(Zend_Http_Client::POST);

        // fetch login token from body
        $parsed_json = json_decode($response->getBody(), true);
        $loginResponse = $parsed_json['login'];

        return array_key_exists('lgtoken', $loginResponse);
    }

    public function getEditToken(Tht_MediaWiki_IDocument $document)
    {
        // login and add cookie to user
        if($this->user->getCookie() === null){
            $this->login($this->user);
        }

        // prepare api params for fetching edit token
        $postParams = array(
            'action'       => 'query',
            'prop'         => 'info|revisions',
            'intoken'      => 'edit',
            'titles'       => $document->getTitle(),
            'format'       => 'json',
            'indexpageids' => 1
        );
        $this->client->setParameterPost( $postParams );
        $response = $this->client->request(Zend_Http_Client::POST);
        
        $parsed_body = json_decode($response->getBody(), true);
        //Zend_Registry::get('logger')->log(print_r($parsed_body, true), 5);
        
        $document->setEdittoken($parsed_body['query']['pages'][($parsed_body['query']['pageids'][0])]['edittoken']);
        //Zend_Registry::get('logger')->log(print_r($document, true), 5);
        return $document;
    }


    public function saveDocument(Tht_MediaWiki_IDocument $document, Tht_MediaWiki_IUser $user = null)
    {
        if($user !== null){
            if($user->getUsername() !== $this->user->getUsername()){
                $this->login($user);
            }
        }

        $document = $this->getEditToken($document);

        $this->client->resetParameters();
        $this->client->setHeaders('Content-type: application/x-www-form-urlencoded');
        $this->client->setHeaders('Accept-encoding: text/html');

        $postParams = array(
            'action' => 'edit',
            'title'  => $document->getTitle(),
            'token'  => $document->getEdittoken(),
            'text'   => $document->getText(),
            'format' => 'json'
        );

        // needed for revision and conflicts
        if($document->getBasetimestamp() !== null){
            $postParams['basetimestamp'] = $document->getBasetimestamp();
        }

        $this->client->setParameterPost($postParams);

        $response = $this->client->request(Zend_Http_Client::POST);
        //Zend_Registry::get('logger')->log(print_r(json_decode($response->getBody(), true), true), 5);
    }


    protected function read(array $postParams)
    {
        $this->client->resetParameters();
        $this->client->setParameterPost($postParams);
        $response = $this->client->request(Zend_Http_Client::POST);

    }


    public function getMarkupByTitle($title)
    {
        $getParams = array(
            'action' => 'query',
            'prop'   => 'revisions',
            'titles' => $title,
            'format' => 'json',
            'rvprop' => 'ids|timestamp|content',
            'indexpageids' => 1
        );

        $this->client->resetParameters();
        $this->client->setParameterGet($getParams);
        $response = $this->client->request(Zend_Http_Client::GET);

        $parsed_response = json_decode($response->getBody(), true);
        
        // no result page found
        if($parsed_response['query']['pageids'][0] == -1){
            return '';
        };

        $page     = $parsed_response['query']['pages'][($parsed_response['query']['pageids'][0])];
        
        $revision = $page['revisions'][0];

        $document = new Tht_MediaWiki_Document();

        $document->setPageid($page['pageid']);
        $document->setNamespace($page['ns']);
        $document->setTitle($page['title']);

        $document->setLastrevid($revision['revid']);
        $document->setBasetimestamp($revision['timestamp']);
        $document->setText($revision['*']);
        
        return $document;
    }

}
