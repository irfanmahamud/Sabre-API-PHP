<?php
include_once APPPATH .  'third_party/SACS-Php-master/soap/XMLSerializer.php';
class AirTicketLLSActivity implements Activity {

    private $config;
    
    public function __construct() {
        $this->config = SACSConfig::getInstance();
    }

    public function run(&$pnr) {
        $soapClient = new SACSSoapClient("AirTicketLLSRQ");
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
            "AirTicketRQ" => array(
                "_namespace" => "http://webservices.sabre.com/sabreXML/2011/10",
                "_attributes" => array(
					"NumResponses"=>"1",
                    "Version" => $this->config->getSoapProperty("AirTicketRQVersion")
                ),
                "OptionalQualifiers" => array(
                    "FlightQualifiers" => array(
                        "VendorPrefs" =>array(
							"Airline"=>array("_attributes"=>array("Code"=>"XX"))
						)
                    ),
					"MiscQualifiers" =>array(
						"Ticket"=>array("_attributes"=>array("Type"=>"ETR"))
					),
                    "PricingQualifiers" => array(
                        "PriceQuote" =>array(
							"Record"=>array("_attributes"=>array("Number"=>"1"))
						)
                    ),					
                )
            )
        );
        return XMLSerializer::generateValidXmlFromArray($requestArray);
    }
}
