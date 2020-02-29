<?php
include_once APPPATH .  'third_party/SACS-Php-master/workflow/Activity.php';
include_once APPPATH .  'third_party/SACS-Php-master/soap/SACSSoapClient.php';
include_once APPPATH .  'third_party/SACS-Php-master/soap_activities/PassengerDetailsNameOnlyActivity.php';
include_once APPPATH .  'third_party/SACS-Php-master/soap/XMLSerializer.php';

class BargainFinderMaxSoapActivity implements Activity {

    private $config;
    
	private $QueryArray=array();
	
    public function __construct($QueryArray = NULL) {
        $this->config = SACSConfig::getInstance();
		$this->QueryArray=$QueryArray;
    }
    
    public function run(&$sharedContext=NULL) {
        $soapClient = new SACSSoapClient("BargainFinderMaxRQ");
        $soapClient->setLastInFlow(false);
        $xmlRequest = $this->getRequest();
		
		//log_message('debug','BargainFinderMaxRQ');
		
		//log_message('debug',$xmlRequest);
		
		$result = $soapClient->doCall($xmlRequest);
		
		//log_message('debug',$result);
		
		return XMLSerializer::xmlToArray($result);
        /* $sharedContext->addResult("BargainFinderMaxRQ", $xmlRequest);
        $sharedContext->addResult("BargainFinderMaxRS", $soapClient->doCall($sharedContext, $xmlRequest));
        return new PassengerDetailsNameOnlyActivity(); */
    }

    public function getRequest() {
		
		$currency = 'BDT';
		
		$rootXml='';
		
		$POS=array("POS" => array(
                "Source" => array(
                    "_attributes" => array("PseudoCityCode"=>$this->config->getSoapProperty("group")),
                    "RequestorID" => array(
                        "_attributes" => array("ID"=>"1", "Type"=>"1"),
                        "CompanyName" => array(
                            "_attributes" => array("Code"=>"TN")
                        )
                    )
                )
            ));
		$POS_xml= XMLSerializer::generateValidXmlFromArray($POS);
		//$rootXml .=$POS_xml;
		
		
		$OriginDestinationInformation_xml= $this->GetOriginDestinationInformationXml();
		//$rootXml .=$OriginDestinationInformation_xml;
		
		$PassengerTypeQuantity_xml=$this->GetPassengerTypeQuantityXml();
		
		$othersElements=array(
			"TravelPreferences" => array(
                "_attributes" => array("ValidInterlineTicket" => "true"),
                "CabinPref" => array("_attributes" => array("Cabin"=>$this->QueryArray["Class"], "PreferLevel"=>"Preferred")),
				"TPA_Extensions"=> array(
					"XOFares"=>array(
						"_attributes"=>array("Value"=>"true"),
						
					),
				),
				"AncillaryFees"=>array(
					"_attributes"=>array("Enable"=>"true","Summary"=>"true"),
					"AncillaryFeeGroup"=>array(
						"_attributes"=>array("Code"=>"BG"),
					)
				),
            ),
            "TravelerInfoSummary" => array(
               
                "AirTravelerAvail" => array(
					"_child" => $PassengerTypeQuantity_xml,
					
                ),
				"PriceRequestInformation"=>array("_attributes" => array("CurrencyCode" => $this->QueryArray["Currency"])),
            ),
            "TPA_Extensions" => array(
                "IntelliSellTransaction" => array(
                    "RequestType" => array("_attributes" => array("Name" => "50ITINS"))
                )
                
            ) 
		);
		
		$othersElements_xml= XMLSerializer::generateValidXmlFromArray($othersElements);
		
		//$rootXml .=$othersElements_xml;
		
		$rootXml = $POS_xml.$OriginDestinationInformation_xml.$othersElements_xml;
        $request = array("OTA_AirLowFareSearchRQ" => array(
            "_attributes" => array("Version" => $this->config->getSoapProperty("BargainFinderMaxRQVersion")),
            "_namespace" => "http://www.opentravel.org/OTA/2003/05",
			"_child" =>$rootXml,
			
        )
        );
        return XMLSerializer::generateValidXmlFromArray($request);
    }
	
	private function GetOriginDestinationInformationXml()
	{
		$i=1;
		$xml='';
		foreach($this->QueryArray["OriginDestinationInformation"] as $odi)
		{
			$DepartureDate = date('Y-m-d',strtotime($odi['DepartureDate'])).'T00:00:00';
			$OriginDestinationInformation=array(
				"OriginDestinationInformation" => array(
					"_attributes" => array("RPH" => $i),
					"DepartureDateTime" =>$DepartureDate,
					"OriginLocation" => array("_attributes" => array("LocationCode"=>$odi['Origin'])),
					"DestinationLocation" => array("_attributes" => array("LocationCode"=>$odi['Destination'])),
					"TPA_Extensions" => array(
						"SegmentType" => array("_attributes" => array("Code" => "O"))
					)
				),
			);
			$xml .= XMLSerializer::generateValidXmlFromArray($OriginDestinationInformation);
			$i++;
		}
		
		return $xml;
	}
	
	private function GetPassengerTypeQuantityXml()
	{
		
		$xml='';
		if(intval($this->QueryArray["NumberOfAdult"]) > 0 )
		{
			//$PassengerTypeQuantity = array( "PassengerTypeQuantity" => array("_attributes" => array("Code" => "ADT", "Quantity" => $this->QueryArray["NumberOfAdult"])));
			$xml .= SabreXMLElementBuilder::generatePassengerTypeQuantityXml('ADT',$this->QueryArray["NumberOfAdult"]);
		}
		if(intval($this->QueryArray["NumberOfChild"]) > 0 )
		{
			//$PassengerTypeQuantity = array( "PassengerTypeQuantity" => array("_attributes" => array("Code" => "CNN", "Quantity" =>$this->QueryArray["NumberOfChild"])));
			$xml .= SabreXMLElementBuilder::generatePassengerTypeQuantityXml('CNN',$this->QueryArray["NumberOfChild"]);
		}		
		
		if(intval($this->QueryArray["NumberOfInfant"]) > 0 )
		{
			//$PassengerTypeQuantity = array( "PassengerTypeQuantity" => array("_attributes" => array("Code" => "CNN", "Quantity" =>$this->QueryArray["NumberOfChild"])));
			$xml .= SabreXMLElementBuilder::generatePassengerTypeQuantityXml('INF',$this->QueryArray["NumberOfInfant"]);
		}
		
		return $xml;
	}

}
