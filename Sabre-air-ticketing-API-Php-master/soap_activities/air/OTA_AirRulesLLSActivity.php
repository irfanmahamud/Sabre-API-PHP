<?php
include_once APPPATH .  'third_party/SACS-Php-master/workflow/Activity.php';
include_once APPPATH .  'third_party/SACS-Php-master/soap/SACSSoapClient.php';
include_once APPPATH .  'third_party/SACS-Php-master/soap_activities/PassengerDetailsNameOnlyActivity.php';
include_once APPPATH .  'third_party/SACS-Php-master/soap/XMLSerializer.php';

class OTA_AirRulesLLSActivity implements Activity {

    private $config;
    
	//private $CI;
	private $queryArray=array();
    public function __construct($params = array()) {
        $this->config = SACSConfig::getInstance();
		$this->queryArray = $params;
    }
    
    public function run(&$sharedContext=NULL) {
        $soapClient = new SACSSoapClient("OTA_AirRulesLLSRQ");
        $soapClient->setLastInFlow(false);
        $xmlRequest = $this->getRequest();
		$result = $soapClient->doCall($xmlRequest);
		return XMLSerializer::xmlToArray($result);
        /* $sharedContext->addResult("BargainFinderMaxRQ", $xmlRequest);
        $sharedContext->addResult("BargainFinderMaxRS", $soapClient->doCall($sharedContext, $xmlRequest));
        return new PassengerDetailsNameOnlyActivity(); */
    }

    private function getRequest() {
		
		$DepartureDate=explode('T',$this->queryArray['segment']['DepartureDateTime']);
		$DepartureDateTime = date('m-d',strtotime($DepartureDate[0]));
		$FareBasisCode=$this->queryArray['FareBasisCodes'];
		$originLocationCode=$this->queryArray['segment']['DepartureAirport'];
		$destinationLocationCode = $this->queryArray['segment']['ArrivalAirport'];
		$marketingAirlineCode=$this->queryArray['segment']['MarketingAirline'];
        $request = array("OTA_AirRulesRQ" => array(
            "_attributes" => array("Version" => $this->config->getSoapProperty("OTA_AirRulesLLSRQVersion")),
            "_namespace" => "http://webservices.sabre.com/sabreXML/2011/10",
            "OriginDestinationInformation" => array(
				"FlightSegment"=>array(
					"_attributes" => array("DepartureDateTime"=>$DepartureDateTime),
					"DestinationLocation" => array("_attributes" => array("LocationCode"=>$destinationLocationCode)),
					"MarketingCarrier" => array("_attributes" => array("Code"=>$marketingAirlineCode)),
					"OriginLocation" => array("_attributes" => array("LocationCode"=>$originLocationCode)),
				),
                
            ),
			"RuleReqInfo" => array(
				"FareBasis"=>array(
					"_attributes" => array("Code"=>$FareBasisCode),
				),
                
            ),
        )
        );
        return XMLSerializer::generateValidXmlFromArray($request);
    }

}
