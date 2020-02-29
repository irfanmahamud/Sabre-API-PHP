<?php
include_once APPPATH .  'third_party/SACS-Php-master/workflow/Activity.php';
include_once APPPATH .  'third_party/SACS-Php-master/soap/SACSSoapClient.php';
include_once APPPATH .  'third_party/SACS-Php-master/soap/XMLSerializer.php';
class AirTicketActivity implements Activity {

    private $config;
	private $QueryArray=array();   
	
    public function __construct($QueryArray = array()) {
        $this->config = SACSConfig::getInstance();
		$this->QueryArray=$QueryArray;
    }

    public function run(&$sharedContext=NULL) {
        $soapClient = new SACSSoapClient("AirTicketRQ");
        $soapClient->setLastInFlow(true);
        $xmlRequest = $this->getRequest();
		$result = $soapClient->doCall($xmlRequest);
		return XMLSerializer::xmlToArray($result);
        /* $sharedContext->addResult("BargainFinderMaxRQ", $xmlRequest);
        $sharedContext->addResult("BargainFinderMaxRS", $soapClient->doCall($sharedContext, $xmlRequest));*/
       //return $xmlRequest; 
                
    }

    private function getRequest() {
		$pnr = $this->QueryArray['pnr'];
		$marketing_airline =  $this->QueryArray['marketing_airline'];
		$LNIATA = $this->config->getSoapProperty("LNIATA");
		
		$passenger =$this->QueryArray['NumberOfAdult'] + $this->QueryArray['NumberOfChild'];
		
		$Record_xml = '';
		for($i=1;$i<=$passenger;$i++)
		{
			$Record = array(
				"Record"=>array("_attributes"=>array("Number"=>$i))
			);
			$Record_xml .= XMLSerializer::generateValidXmlFromArray($Record);
		}
		
        $requestArray = array(
            "AirTicketRQ" => array(
               
                "_attributes" => array(
					"xmlns" => "http://services.sabre.com/sp/air/ticket/v1",
                    "version" => $this->config->getSoapProperty("AirTicketRQVersion")
                ),
				"DesignatePrinter" => array(
					"Printers" => array(
						"Hardcopy"=>array(
							"_attributes"=>array("LNIATA"=>$LNIATA,"Spacing"=>"1")
						),
						"InvoiceItinerary"=>array(
							"_attributes"=>array("LNIATA"=>$LNIATA)
						),						
						"Ticket" => array(
							"_attributes"=>array("CountryCode"=>"BD")
						)
					),
				),
				"Itinerary"=>array(
					"_attributes"=>array(
						"ID"=> $pnr
					)
				),
				"Ticketing"=>array(
					"FlightQualifiers"=>array(
						"VendorPrefs"=>array(
							"Airline"=>array(
								"_attributes"=>array(
									"Code"=>$marketing_airline
								)
							)
						)
					),
					"FOP_Qualifiers"=>array(

					),
					"PricingQualifiers"=>array(
						"PriceQuote" =>array(
							"_child" => $Record_xml,
						)					
					)
				),
                "PostProcessing"=>array(
					"EndTransaction"=>array(
						"Source" => array(
							"_attributes"=> array(
								"ReceivedFrom"=>"DESHIFLIGHT"
							)
						)
					)
				)
            )
        );
        return XMLSerializer::generateValidXmlFromArray($requestArray);
    }
}
