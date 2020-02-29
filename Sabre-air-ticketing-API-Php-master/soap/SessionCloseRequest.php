<?php

include_once APPPATH .  'third_party/SACS-Php-master/configuration/SACSConfig.php';
include_once APPPATH .  'third_party/SACS-Php-master/soap/SACSSoapClient.php';
include_once APPPATH .  'third_party/SACS-Php-master/soap/XMLSerializer.php';
class SessionCloseRequest {

    private $config;
    
    public function __construct() {
        $this->config = SACSConfig::getInstance();

    }
	
	public function executeRequest($security) {
		
		$sacsClient = new SACSClient();
        $result = $sacsClient->doCall(SACSSoapClient::createMessageHeader('SessionCloseRQ') . $this->createSecurityHeader($security), $this->createRequestBody(), 'SessionCloseRQ');
		
		return XMLSerializer::xmlToArray($result);
    }
    
    private function createSecurityHeader($security) {
        $security_arr = array("Security" => array(
                "_namespace" => "http://schemas.xmlsoap.org/ws/2002/12/secext",
				"_attributes" => array(
					"soap-env:mustUnderstand"=>"0",
					
				),
                "BinarySecurityToken" => array(
                    "_attributes" => array("EncodingType" => "Base64Binary", "valueType" => "String"),
                    "_value" => $security
                )
            )
        );
        return XMLSerializer::generateValidXmlFromArray($security_arr,'eb');
    }
    
    private function createRequestBody() {
        $result = array("SessionCloseRQ" => array(
            "POS" => array(
                "Source" => array("_attributes"=>array(
                    "PseudeCityCode" => $this->config->getSoapProperty("group")
					)
                )
            )
        ));
		return XMLSerializer::generateValidXmlFromArray($result);
        
    }
    
   
}
