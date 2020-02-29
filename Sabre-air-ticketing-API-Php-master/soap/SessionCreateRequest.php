<?php
include_once APPPATH .  'third_party/SACS-Php-master/configuration/SACSConfig.php';
include_once APPPATH .  'third_party/SACS-Php-master/soap/SACSSoapClient.php';
include_once APPPATH .  'third_party/SACS-Php-master/soap/XMLSerializer.php';
class SessionCreateRequest {

    private $config;
    
    public function __construct() {
        $this->config = SACSConfig::getInstance();

    }
    
    public function executeRequest() {
		
		$sacsClient = new SACSClient();
        $result = $sacsClient->doCall(SACSSoapClient::createMessageHeader('SessionCreateRQ') . $this->createSecurityHeader(), $this->createRequestBody(), 'SessionCreateRQ');
		
		return XMLSerializer::xmlToArray($result);
    }
    
    private function createSecurityHeader() {
		
		        $messageHeader = array("Security" => array(
                "_namespace" => "http://schemas.xmlsoap.org/ws/2002/12/secext",
                "UsernameToken" => array(
                    "Username" => $this->config->getSoapProperty("userId"),
                    "Password" => $this->config->getSoapProperty("clientSecret"),
                    "Domain" => $this->config->getSoapProperty("domain"),
                    "Organization" => $this->config->getSoapProperty("group")
                ),
                
            )
        );
        return XMLSerializer::generateValidXmlFromArray($messageHeader,'wsse');
		
       
    }
    
    private function createRequestBody() {
        $result = array("SessionCreateRQ" => array(
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
