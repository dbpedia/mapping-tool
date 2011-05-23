<?php

class Tht_MediaWiki_DBpedia extends Tht_MediaWiki_Reader_Core
{
    public function __construct($apiUrl)
    {
        parent::__construct($apiUrl);
    }

    /**
     * returns a list of all pages with id and some more
     * informations of a given namespace
     *
     * @param integer $ns requested MediaWiki namespace
     * @return array of pages with metainfo corresponding to given namespace
     */
    public function getPagesByNamespace($ns)
    {
        // flag to to stop querying MediaWiki
        // if result list is empty
        $endIsReached = false;

        // set starting point for next query
        // as not existant
        $apfrom = "";

        // initialize list to serialize
        $templateList = array();

        // MediWiki list of pages is not completely downloaded
        while (!$endIsReached) {

            // read the list of pages concerning to
            // a given namespace
            $getParameters = array(
                'action'      => 'query',
                'list'        => 'allpages',
                'apnamespace' => $ns,
                'aplimit'     => 500,
                'format'      => 'json',
                'apfrom'      => $apfrom
            );

            $this->client->resetParameters();
            $this->client->setParameterGet($getParameters);
            $response = $this->client->request(Zend_Http_Client::GET);

            // decode json output
            $array = json_decode($response->getBody(), true);

            // iterate over MediaWiki pages matching the search query
            // and add the page to the templateList
            foreach($array['query']['allpages'] as $nb => $page){
                $templateList[] = $page;
            }

            // if end of list is reached stop while loop
            // else continue reading list with the proposed
            // apfrom search term
            if(!isset($array['query-continue']['allpages']['apfrom']) OR $array['query-continue']['allpages']['apfrom'] == ''){
                $endIsReached = true;
            } else {
                // set the search term for the next run
                $apfrom = $array['query-continue']['allpages']['apfrom'];
            }
        }

        return $templateList;
    }
    
    
    /**
    * returns a list of all pages with content of
    * given namespace
    *
    * @param integer $ns requested MediaWiki namespace
    * @return array of pages within given namespace with content
    */
    public function getPageContentByNamespace($ns)
    {
        // max number of pages to load in one http request
        $pidlimit = Zend_Registry::get('config')->dbpedia->api->synchronous->pageload->limit;
    
        // fetch a list of all pages of given namespace
        $templateList = $this->getPagesByNamespace($ns);

        // temporaray page id collection for the next http call
        $pids = array();

        // instantiate list/data holder for MediaWiki page represantations
        $list = new Tht_MediaWiki_DocumentList();

        // walk through the source list of pages of namespace
        for($i = 0; $i < count($templateList); $i++){

            // collect the pid of the current page of namespace
            $pids[] = intval($templateList[$i]['pageid']);

            // every $pidlimit walks make a http call to the MediaWiki for retrieving content
            if((($i+1) % $pidlimit == 0) OR ($i == count($templateList)-1)){

                // prepare paramters for http call
                $getParameters = array(
                    'action'       => 'query',
                    'prop'         => 'revisions',
                    'indexpageids' => true,
                    'pageids'      => implode('|', $pids),
                    'rvprop'       => 'timestamp|content',
                    'format'       => 'json'
                );

                // execute http call
                $this->client->resetParameters();
                $this->client->setParameterGet($getParameters);
                $response = $this->client->request(Zend_Http_Client::GET);
               
                // decode json of http response
                $array = json_decode($response->getBody(), true);

                // add each listed page to page list
                foreach($array['query']['pages'] as $page){

                    // create document from MediaWiki page
                    $document = new Tht_MediaWiki_Document();
                    $document->setTitle($page['title']);
                    $document->setNamespace($page['ns']);
                    $document->setPageid($page['pageid']);
                    $document->setBasetimestamp($page['revisions'][0]['timestamp']);
                    $document->setText($page['revisions'][0]['*']);

                    // add document to documentList
                    $list->offsetSet(null, $document);
                }

                // reset pid list for next http call
                $pids = array();
            }
        }

        // return the <Tht_MediaWiki_DocumentList> documentList of <Tht_MediaWiki_Document> documents
        return $list;
    }



   public function getLanguageNamespaces(){
        $getParams = array(
            'action' => 'query',
            'meta'   => 'siteinfo',
            'siprop' => 'namespaces',
            'format' => 'json'
  
        );

       $this->client->resetParameters();
       $this->client->setParameterGet($getParams);
       $response = $this->client->request(Zend_Http_Client::GET);
       $parsed_response = json_decode($response->getBody(), true);


       


       
      $namespaces =    $parsed_response["query"]["namespaces"];
      $languages = array();
      foreach($namespaces as $key => $value)
      {

             $lang= Array();
            if (preg_match("/^(Mapping [a-z]{2})$/", $value["*"], $matches)) {
               $lang["name"]=substr($matches[0],-2);
               $languages[] =  $lang;
            }

      }

      //english language workaround
      $lang=Array();
      $lang["name"]="en";
       $languages[] =  $lang;
       //end of workaround

       $getParams = array(
            'action' => 'query',
            'meta'   => 'siteinfo',
            'siprop' => 'languages',
            'format' => 'json'
  
        );
        $this->client->resetParameters();
       $this->client->setParameterGet($getParams);
       $response = $this->client->request(Zend_Http_Client::GET);
        $parsed_response = json_decode($response->getBody(), true);
       $allLanguages =    $parsed_response["query"]["languages"];
       $allLanguagesCollapsed = $this->array_collapse($allLanguages, "code","*");

      foreach($languages as &$value ) {

      
         $value["friendlyName"]=$allLanguagesCollapsed[$value["name"]];
         
      }


      return $languages;
   }
   private function array_collapse($arr, $x, $y) {
      $carr = array();
      while ($el = current($arr)) {
        $carr[ $el[$x] ] = $el[$y];
        next($arr);
      }
    return $carr;
   }
    public function getLanguageByName($langName){
        $getParams = array(
            'action' => 'query',
            'meta'   => 'siteinfo',
            'siprop' => 'namespaces',
            'format' => 'json'
  
        );

       $this->client->resetParameters();
       $this->client->setParameterGet($getParams);
       $response = $this->client->request(Zend_Http_Client::GET);
       $parsed_response = json_decode($response->getBody(), true);


     

       
      $namespaces =    $parsed_response["query"]["namespaces"];
      $namespaceId = $langName=="en"?204:null;//hack for the english language
      
      $language = array();
      foreach($namespaces as $key => $value)
      {


            if (preg_match("/^(Mapping ".$langName.")$/", $value["*"], $matches)) {

            $namespaceId = $value["id"];
            break;
            }

      }

      if($namespaceId!=null){

         $language["name"]=$langName;
         $language["namespace"]=$namespaceId;
         $language["mappingAlias"]= $langName=='en'?"Mapping" : "Mapping_".$langName;
         $language["mappingRoute"]= "mappings/".$langName;
         $wikiURL = Zend_Registry::get('config')->wikipedia->wiki->url;
         $apiURL = Zend_Registry::get('config')->wikipedia->api->url;
         $language["wikipediaURL"]= str_replace("en",$langName,$wikiURL)  ;
         $language["wikipediaAPIURL"] = str_replace("en",$langName,$apiURL);
         $wr = new Tht_MediaWiki_Wikipedia($language["wikipediaAPIURL"] );
         $language["wikipediaTemplateAlias"] = $wr->getTemplateAlias();
         return $language;
      }
      
      
   }

}