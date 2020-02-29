<?php
include_once APPPATH .  'third_party/SACS-Php-master/workflow/Activity.php';
include_once APPPATH .  'third_party/SACS-Php-master/soap/SACSSoapClient.php';
include_once APPPATH .  'third_party/SACS-Php-master/soap/XMLSerializer.php';
include_once APPPATH .  'third_party/SACS-Php-master/library/calculateage.php';

class CreatePassengerNameRecordActivity implements Activity {

    private $config;
    
	private $QueryArray=array();
	var	$ssrSecureFlight_xml='';
	var	$ssrService_xml = '';
	var $ssrAdvancePassenger_xml='';
		
	private $nameSelect=array(
		'ADT'=>array(),
		'CNN'=>array(),
		'INF'=>array(),
		
	);
	
    public function __construct($QueryArray = array()) {
        $this->config = SACSConfig::getInstance();
		$this->QueryArray=$QueryArray;
    }
    
    public function run(&$sharedContext=NULL) {
        $soapClient = new SACSSoapClient("CreatePassengerNameRecordRQ");
        $soapClient->setLastInFlow(true);
        $xmlRequest = $this->getRequest();
		
		//log_message('debug','CreatePassengerNameRecordRQ');
		//log_message('debug',$xmlRequest);
		//pre($xmlRequest);
		$result = $soapClient->doCall($xmlRequest);
		
		//log_message('debug',$result);
		
		return XMLSerializer::xmlToArray($result);
        /* $sharedContext->addResult("BargainFinderMaxRQ", $xmlRequest);
        $sharedContext->addResult("BargainFinderMaxRS", $soapClient->doCall($sharedContext, $xmlRequest));*/
       // return $xmlRequest; 
	  // return $this->getAirBookElement();
    }
    private function getRequest() {
		$odiairline = reset($this->QueryArray["OriginDestinationInformation"]);
		$segment=reset($odiairline['FlightSegment']);
		$airline = $segment['MarketingAirline'];
		$airbookchild = $this->getAirBookElement();
		
/* 									'Service'=>array(
								'_attributes'=>array('SSR_Code'=>'OTHS'),
								'PersonName'=>array('_attributes'=>array('NameNumber'=>'1.1')),
								'Text'=>'test',
								'VendorPrefs'=>array('Airline'=>array('_attributes'=>array('Code'=>$airline)))
							) */
							
        $requestArray = array(
            "CreatePassengerNameRecordRQ" => array(
                "_attributes" => array(
                    "version" => $this->config->getSoapProperty("CreatePassengerNameRecordRQVersion")
                ),
                "_namespace" => "http://services.sabre.com/sp/reservation/v2",
				'TravelItineraryAddInfo'=>array(
					'_child'=>$this->getTravelItineraryAddInfo(),
				),
				"AirBook" => array(
					'_child' => $airbookchild
				),
				'AirPrice'=>array(
					'_child' => $this->getAirPriceElement(),
					
				),
				"SpecialReqDetails"=>array(
						'AddRemark'=>array(
							'RemarkInfo'=>array(
							),
						),
						'SpecialService'=>array(
						'SpecialServiceInfo'=>array(
							'_child'=>$this->ssrAdvancePassenger_xml.$this->ssrSecureFlight_xml.$this->ssrService_xml,

						),
					),
				),
                "PostProcessing" => array(
				'_attributes'=>array( "RedisplayReservation" => 'true'),
					'EndTransaction'=>array(
						'Source'=>array('_attributes'=>array('ReceivedFrom'=>'DESHIFLIGHT'))
					),
                   ),
            ),
            
        );
        return XMLSerializer::generateValidXmlFromArray($requestArray);
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

	private function GetOriginDestinationInformationXml()
	{
		$i=1;
		$xml='';
		
		$totalPassenger = $this->QueryArray["NumberOfAdult"]+$this->QueryArray["NumberOfChild"];
		
		if(!is_array($this->QueryArray["OriginDestinationInformation"]))
		{
			return '';
		}
		$flight_xml='';
		foreach($this->QueryArray["OriginDestinationInformation"] as $odi)
		{
			
			foreach($odi['FlightSegment'] as $flightSegment)
			{
			
			    
				$destinationLocation = $flightSegment['ArrivalAirport'];
				
				$marketingAirlineCode = $flightSegment['MarketingAirline'];
				$marketingAirlineFlightNumber = $flightSegment['FlightNumber'];
				$originLocation = $flightSegment['DepartureAirport'];
				$departureDateTime = $flightSegment['DepartureDateTime'];
				$flightNumber = $flightSegment['FlightNumber'];
				$numberInParty = $totalPassenger;
				$resBookDesigCode =$flightSegment['ResBookDesigCode'];
			$FlightSegment=array(
				"FlightSegment" => array(
                            "_attributes" => array(
                                "DepartureDateTime" => $departureDateTime,
                                "FlightNumber" => $flightNumber,
                                "NumberInParty" => $numberInParty,
                                "ResBookDesigCode" => $resBookDesigCode,
                                "Status" => "NN"
                            ),
                            "DestinationLocation" => array("_attributes" => array("LocationCode" => $destinationLocation)),
                            "MarketingAirline" => array("_attributes" => array("Code" => $marketingAirlineCode, "FlightNumber" => $marketingAirlineFlightNumber)),
                            "OriginLocation" => array("_attributes" => array("LocationCode" => $originLocation))
                        ),
				);
				$flight_xml .= XMLSerializer::generateValidXmlFromArray($FlightSegment);
			}
			$i++;
		}	
			$OriginDestinationInformation = array(
					'OriginDestinationInformation'=>array(
						'_child'=>$flight_xml,
					),
			);
			
			$xml .= XMLSerializer::generateValidXmlFromArray($OriginDestinationInformation);
			
		
		
		return $xml;
	}	
	
	private function getAirBookElement()
	{
		$haltOnStatus = array('HL','KK','LL','NN','NO','UC','US');
		$hoc_xml='';
		foreach($haltOnStatus as $hoc)
		{
			$haltOn=array('HaltOnStatus'=>array('_attributes'=>array('Code'=>$hoc)));
			$hoc_xml .= XMLSerializer::generateValidXmlFromArray($haltOn);
		}
		$airBook_xml = $hoc_xml.$this->GetOriginDestinationInformationXml();
		return $airBook_xml;
	}
	private function getTravelItineraryAddInfo()
	{
		$AgencyInfo = array(
			'AgencyInfo'=>array(
				'Address'=>array(
					'AddressLine'=>'My Trip Tourism',
					'CityName' => 'Dhaka',
					'CountryCode'=>'BD',
					'PostalCode'=>'1217',
					'StreetNmbr'=>'74 kakrail'
				),
				'Ticketing'=>array('_attributes'=>array('TicketType'=>'7TAW/')),
			),
		);
		$AgencyInfo_xml =  XMLSerializer::generateValidXmlFromArray($AgencyInfo);
		

		if(!is_array($this->QueryArray["travellerInfo"]))
		{
			return '';
		}		
		
		$adult_pass=array();
		
		$contact_xml = '';
		$person_name_xml = '';
		$ssrSecureFlight_xml='';
		$ssrService_xml = '';
		$i=1;
		foreach($this->QueryArray["travellerInfo"] as $tv)
		{
			$pass_type = strtoupper($tv['TravelerType']);
			
			$NameNumber = $i.'.1';
			//$this->nameSelect[$pass_type][]=$NameNumber;
			
			$phone = $tv['Number'];
			
			if(strcmp('ADT',$pass_type)===0)
			{
				if(strcmp('MR',$tv['Prefix'])===0)
					$gender='M';
				else
					$gender='F';
				$adult_pass[]=$NameNumber;
				$PersonName=array(
				'PersonName'=>array(
				'_attributes'=>array(
				'NameNumber'=>$NameNumber,
				'PassengerType'=>$pass_type
				),
				'GivenName'=>ucfirst($tv['First']).' '.$tv['Prefix'],
				'Surname'=>$tv['Last']
					)
				);
				
				if($tv['Number'] != '')
				{
					$ContactNumber = array('ContactNumber'=>array('_attributes'=>array('NameNumber'=>$NameNumber,'Phone'=>$phone,'PhoneUseType'=>'H')));
					
					$contact_xml .= XMLSerializer::generateValidXmlFromArray($ContactNumber);
				}
			}			
			if(strcmp('CNN',$pass_type)===0)
			{
				if(strcmp('MSTR',$tv['Prefix'])===0)
					$gender='M';
				else
					$gender='F';
				
				
				$age = ageCalculator( new DateTime($tv['DOB']), '%y' );
				if($age==0) $age=1;
				$NameReference = 'C'.str_pad($age, 2, '0', STR_PAD_LEFT);
				
				$PersonName=array('PersonName'=>array(
					'_attributes'=>array('NameNumber'=>$NameNumber,'NameReference'=>$NameReference,'PassengerType'=>$pass_type),
					'GivenName'=>ucfirst($tv['First']).' '.$tv['Prefix'],
					'Surname'=>$tv['Last']
					)
				);
				
				$ssrService=array(
					'Service'=>array(
						'_attributes'=>array('SSR_Code'=>'CHLD'),
						'PersonName'=>array(
							'_attributes'=>array(
								'NameNumber'=>$NameNumber
							),
						),
						'Text'=>date('dMy',strtotime($tv['DOB'])),
					),
				);
				
				$this->ssrService_xml .=  XMLSerializer::generateValidXmlFromArray($ssrService);
			}
			if(strcmp('INF',$pass_type)===0)
			{
				if(strcmp('MSTR',$tv['Prefix'])===0)
					$gender='MI';
				else
					$gender='FI';
				$age = ageCalculator( new DateTime($tv['DOB']), '%m' );
				if($age==0) $age=1;
				$NameReference = 'I'.str_pad($age, 2, '0', STR_PAD_LEFT);
				
				$PersonName=array('PersonName'=>array('_attributes'=>array('Infant'=>'true','NameNumber'=>$NameNumber,'NameReference'=>$NameReference,'PassengerType'=>$pass_type),
				'GivenName'=>ucfirst($tv['First']).' '.$tv['Prefix'],
				'Surname'=>ucfirst($tv['Last'])
				)
				);
				
				$NameNumber =array_shift($adult_pass);
				$ssrService=array(
					'Service'=>array(
						'_attributes'=>array('SSR_Code'=>'INFT'),
						'PersonName'=>array(
							'_attributes'=>array(
								'NameNumber'=>$NameNumber
							),
						),
						'Text'=>ucfirst($tv['Last']).'/'.ucfirst($tv['First']).' '.$tv['Prefix'].'/'.date('dMy',strtotime($tv['DOB'])),
					),
				);
				
				$this->ssrService_xml .=  XMLSerializer::generateValidXmlFromArray($ssrService);				
			}			
			
			
			$person_name_xml .= XMLSerializer::generateValidXmlFromArray($PersonName);
			
			$ssrAdvancePassenger=array(
			'AdvancePassenger'=>array(
				'_attributes'=>array('SegmentNumber'=>'A'),
				'Document'=>array(
					'_attributes'=>array(					
						'ExpirationDate'=>date('Y-m-d',strtotime($tv['exp_date_passport'])),
						'Number'=>$tv['passport_no'],
						'Type'=>'P'
					),
					'IssueCountry'=>$tv['issue_country'],
					'NationalityCountry'=>$tv['nationality']
				),
				'PersonName'=>array(
						'_attributes'=>array(
							'DateOfBirth'=>date('Y-m-d',strtotime($tv['DOB'])),
							'DocumentHolder'=>'true',
							'Gender'=>$gender,
							'NameNumber'=>$NameNumber,
						),
						'GivenName'=>ucfirst($tv['First']).' '.$tv['Prefix'],
						'Surname'=>ucfirst($tv['Last'])
				)	
			)
			);
			
			$this->ssrAdvancePassenger_xml .= XMLSerializer::generateValidXmlFromArray($ssrAdvancePassenger);
			
			$ssrSecureFlight=array(
				'SecureFlight'=>array(
					'_attributes'=>array('SegmentNumber'=>'A'),
					'PersonName'=>array(
						'_attributes'=>array(
							'DateOfBirth'=>date('Y-m-d',strtotime($tv['DOB'])),
							'Gender'=>$gender,
							'NameNumber'=>$NameNumber,
						),
						'GivenName'=>ucfirst($tv['First']).' '.$tv['Prefix'],
						'Surname'=>ucfirst($tv['Last'])
					)
				)
			);
			
			$this->ssrSecureFlight_xml .= XMLSerializer::generateValidXmlFromArray($ssrSecureFlight);
			


			
			$i++;
		}
		
		$ContactNumbers=array('ContactNumbers'=>array('_child'=>$contact_xml));
		
		$contacts_xml = XMLSerializer::generateValidXmlFromArray($ContactNumbers);
		
		$CustomerInfo = array(
		'CustomerInfo'=>array(
			'_child'=>$contacts_xml.$person_name_xml,
		)
		);
		
		$CustomerInfo_xml = XMLSerializer::generateValidXmlFromArray($CustomerInfo);
		$main_xml =$AgencyInfo_xml.$CustomerInfo_xml;
		return $main_xml;
	}	
	
	public function getAirPriceElement()
	{
		$main_xml = '';
		$ns_xml = '';
/* 		if(intval($this->QueryArray["NumberOfAdult"]) > 0 )
		{			
			pre($this->nameSelect);
			foreach($this->nameSelect['ADT'] as $adt)
			{
				$NameSelect= array(
					'NameSelect'=>array('_attributes'=>array('NameNumber'=>$adt))
				);
				$ns_xml .= XMLSerializer::generateValidXmlFromArray($NameSelect);
			} 
			$ns_xml .= SabreXMLElementBuilder::generatePassengerTypeQuantityXml('ADT',$this->QueryArray["NumberOfAdult"]);
			
		} */
		
		if(intval($this->QueryArray["NumberOfAdult"]) > 0 )
		{		
			$PassengerType=array(
				'PassengerType'=>array(
					'_attributes'=>array(
						'Code'=>'ADT',
						'Quantity'=> $this->QueryArray["NumberOfAdult"]
					)
				)
			);
			$ns_xml .=  XMLSerializer::generateValidXmlFromArray($PassengerType);
		}
		
		if(intval($this->QueryArray["NumberOfChild"]) > 0 )
		{		
			$PassengerType=array(
				'PassengerType'=>array(
					'_attributes'=>array(
						'Code'=>'CNN',
						'Quantity'=> $this->QueryArray["NumberOfChild"]
					)
				)
			);
			$ns_xml .=  XMLSerializer::generateValidXmlFromArray($PassengerType);
		}
		if(intval($this->QueryArray["NumberOfInfant"]) > 0 )
		{		
			$PassengerType=array(
				'PassengerType'=>array(
					'_attributes'=>array(
						'Code'=>'INF',
						'Quantity'=> $this->QueryArray["NumberOfInfant"]
					)
				)
			);
			$ns_xml .=  XMLSerializer::generateValidXmlFromArray($PassengerType);
		}		
		  $airBook=array(
			'PriceRequestInformation'=>array(
				'_attributes'=>array('Retain'=>'true'),
				'OptionalQualifiers'=>array(
					'FOP_Qualifiers'=>array(
						'BasicFOP'=>array('_attributes'=>array('Type'=>'CK'))
						),
					'PricingQualifiers'=>array(
						'_child'=>$ns_xml,
						'SpecificPenalty'=>array(
							'Changeable'=>array('_attributes'=>array('Any'=>'true'))
						)
					),
				)
			),
		  );	
			
			$main_xml .= XMLSerializer::generateValidXmlFromArray($airBook);
			
		
		return $main_xml;
	}
}
