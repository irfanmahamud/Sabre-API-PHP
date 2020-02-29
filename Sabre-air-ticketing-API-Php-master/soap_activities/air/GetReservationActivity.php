<?php
include_once APPPATH .  'third_party/SACS-Php-master/workflow/Activity.php';
include_once APPPATH .  'third_party/SACS-Php-master/soap/SACSSoapClient.php';
include_once APPPATH .  'third_party/SACS-Php-master/soap/XMLSerializer.php';

class GetReservationActivity implements Activity {

    private $config;
    
    public function __construct() {
        $this->config = SACSConfig::getInstance();
    }

    public function run(&$pnr) {
        $soapClient = new SACSSoapClient("GetReservationRQ");
        $soapClient->setLastInFlow(true);
        $xmlRequest = $this->getRequest($pnr);
		$result = $soapClient->doCall($xmlRequest);
		return XMLSerializer::xmlToArray($result);
        /* $sharedContext->addResult("BargainFinderMaxRQ", $xmlRequest);
        $sharedContext->addResult("BargainFinderMaxRS", $soapClient->doCall($sharedContext, $xmlRequest));*/
        //return $xmlRequest; 
                
    }

    private function getRequest($pnr) {
		
		$state = "Statelful";
        $requestArray = array(
            "GetReservationRQ" => array(
                "_namespace" => "http://webservices.sabre.com/pnrbuilder/v1_19",
                "_attributes" => array(
                    "Version" => $this->config->getSoapProperty("GetReservationRQVersion")
                ),
				"Locator"=>$pnr,
				"RequestType"=>$state
                "ReturnOptions" => array(
					"_attributes"=>array("PriceQuoteServiceVersion"=>"3.2.0")
                    "SubjectAreas" => array(
                        "SubjectArea" => "PRICE_QUOTE"
                    ),
					"ViewName"=>"Simple",
					"ResponseFormat"=>"STL"
                ),
                "UniqueID" => array(
                    "_attributes" => array("ID" => $pnr)
                )
            )
        );
        return XMLSerializer::generateValidXmlFromArray($requestArray);
    }
}
