<?php
include_once APPPATH .  'third_party/SACS-Php-master/soap/XMLSerializer.php';
class EndTransactionActivity implements Activity {

    private $config;
    
    public function __construct() {
        $this->config = SACSConfig::getInstance();
    }

    public function run(&$pnr) {
        $soapClient = new SACSSoapClient("EndTransactionRQ");
        $soapClient->setLastInFlow(true);
        $xmlRequest = $this->getRequest($pnr);
		$result = $soapClient->doCall($xmlRequest);
		return XMLSerializer::xmlToArray($result);
        /* $sharedContext->addResult("BargainFinderMaxRQ", $xmlRequest);
        $sharedContext->addResult("BargainFinderMaxRS", $soapClient->doCall($sharedContext, $xmlRequest));*/
        //return $xmlRequest; 
                
    }

    private function getRequest($pnr) {
        $requestArray = array(
            "EndTransactionRQ" => array(
                "_namespace" => "http://webservices.sabre.com/sabreXML/2011/10",
                "_attributes" => array(
                    "Version" => $this->config->getSoapProperty("EndTransactionRQVersion")
                ),
                "EndTransaction" => array(
                    "_attributes" => array("Ind" =>"true")
                ),
                "Source" => array(
                    "_attributes" => array("ReceivedFrom" =>"SWS TEST")
                ),				
            )
        );
        return XMLSerializer::generateValidXmlFromArray($requestArray);
    }
}
