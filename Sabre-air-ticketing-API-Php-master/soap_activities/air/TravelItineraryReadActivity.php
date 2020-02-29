<?php
include_once APPPATH .  'third_party/SACS-Php-master/soap/XMLSerializer.php';
class TravelItineraryReadActivity implements Activity {

    private $config;
    
    public function __construct() {
        $this->config = SACSConfig::getInstance();
    }

    public function run(&$pnr) {
        $soapClient = new SACSSoapClient("TravelItineraryReadRQ");
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
            "TravelItineraryReadRQ" => array(
                "_namespace" => "http://services.sabre.com/res/tir/v3_6",
                "_attributes" => array(
                    "Version" => $this->config->getSoapProperty("TravelItineraryReadRQVersion")
                ),
                "MessagingDetails" => array(
                    "SubjectAreas" => array(
                        "SubjectArea" => "PNR"
                    )
                ),
                "UniqueID" => array(
                    "_attributes" => array("ID" => $pnr)
                )
            )
        );
        return XMLSerializer::generateValidXmlFromArray($requestArray);
    }
}
