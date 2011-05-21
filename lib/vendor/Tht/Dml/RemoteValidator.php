<?php

class Tht_Dml_RemoteValidator
{
    protected $client;
    protected $validatorResponse;

    public function validateMarkup($title, $markup)
    {
        list($ns, $tmp) = explode(':', $title);
        $xmlData = '
            <mediawiki xmlns="http://www.mediawiki.org/xml/export-0.4/"
                       xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                       xsi:schemaLocation="http://www.mediawiki.org/xml/export-0.4/ http://www.mediawiki.org/xml/export-0.4.xsd"
                       version="0.4"
                       xml:lang="en">
                <page>
                    <title>' . htmlentities($title) . '</title>
                    <id>0</id>
                    <revision>
                        <id>0</id>
                        <text xml:space="preserve">' . htmlentities($markup) . '</text>
                    </revision>
                </page>
            </mediawiki>
        ';
        switch($ns){
            case 'Mapping':
                $route = 'mappings/en';
                break;
            case 'Class':
            case 'OntologyProperty':
            case 'Datatype':
                $route = 'ontology';
                break;
            default:
                Tht_Helper_Header::badRequest();
                Tht_Helper_Header::JS();
                echo json_encode(
                    array(
                        'message' => 'unknown namespace ' . $ns
                    )
                );
                die();
        }

        $validateServiceUrl = Zend_Registry::get('config')->dbpedia->service->validator->url;
        $validateServiceUrl = str_replace('{route}', $route, $validateServiceUrl);
        $validateServiceUrl = str_replace('{title}', urlencode($title), $validateServiceUrl);

        //Zend_Uri::setConfig(array('allow_unwise' => true));

        $this->client = new Zend_Http_Client($validateServiceUrl, array(
            'maxredirects' => 0,
            'timeout'      => 30,
            'adapter'      => 'Zend_Http_Client_Adapter_Socket',
            'useragent'    => 'Mapping Tool v0.1',
            'keepalive'    => false
        ));

        //Zend_Uri::setConfig(array('allow_unwise' => false));
        $this->client->setHeaders('Content-type: application/xml');

        $response = $this->client->setRawData($xmlData, 'text/xml')->request('POST');

        $this->validatorResponse = json_decode(json_encode(simplexml_load_string($response->getBody())), true);
        return $this->validatorResponse;
    }
    
    public function isValid(){
        if(!isset($this->validatorResponse['record'])){
            return true;
        }
        return false;
    }
    
    public function getHtmlListOfErrors()
    {
        // if the record has only one error message, the
        // message is not nested in an array, to fix this
        // an array is wrapped to simulate a list of multiple
        // errors
        $result = $this->validatorResponse;
        $errorMessage = array();
        if(!isset($result['record'][0])){
            $result['record'] = array($result['record']);
        }
        
        // handle the list of errors
        foreach($result['record'] as $error){
            $errorMessages[] = $error['message'];
        }
        
        return implode('<br><br>', $errorMessages);
    }
}