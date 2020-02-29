<?php
include_once APPPATH .  'third_party/SACS-Php-master/soap/XMLSerializer.php';
class DesignatePrinterActivity implements Activity {

    private $config;
    
    public function __construct() {
        $this->config = SACSConfig::getInstance();
    }

    public function run(&$pnr) {
        $soapClient = new SACSSoapClient("DesignatePrinterRQ");
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
            "DesignatePrinterRQ" => array(
                "_namespace" => "http://webservices.sabre.com/sabreXML/2011/10",
                "_attributes" => array(
                    "Version" => $this->config->getSoapProperty("DesignatePrinterRQVersion")
                ),
                "Printers" => array(
                    "Ticket" => array(
                        "_attributes"=>array("CountryCode"=>"BD","LNIATA"=>$this->config->getSoapProperty("LNIATA"))
                    )
                ),
            )
        );
        return XMLSerializer::generateValidXmlFromArray($requestArray);
    }
}
