<?php

include_once APPPATH .  'third_party/SACS-Php-master/configuration/SACSConfig.php';
include_once APPPATH .  'third_party/SACS-Php-master/soap/SACSSoapClient.php';
include_once APPPATH .  'third_party/SACS-Php-master/soap/XMLSerializer.php';
class SessionRefreshRequest {

    private $config;
    
    public function __construct() {
        $this->config = SACSConfig::getInstance();
    }
    
	    public function executeRequest($security) {
		
		$sacsClient = new SACSClient();
        $result = $sacsClient->doCall(SACSSoapClient::createMessageHeader('OTA_PingRQ') . $this->createSecurityHeader($security), $this->createRequestBody(), 'OTA_PingRQ');
		
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
		$Timestamp = date('Y-m-d').'T'.date('H:i:s').'Z';
        $result = array("OTA_PingRQ" => array(
				"_namespace" => "http://www.opentravel.org/OTA/2003/05",
                "_attributes" => array("TimeStamp"=>$Timestamp,
				"Version" => $this->config->getSoapProperty("OTA_PingRQVersion")
				),
				"EchoData"=>"Are You There"
            )
        );
        return XMLSerializer::generateValidXmlFromArray($result);
    }
    
}
