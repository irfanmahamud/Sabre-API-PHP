<?php

include_once APPPATH .  'third_party/SACS-Php-master/workflow/SharedContext.php';
include_once APPPATH .  'third_party/SACS-Php-master/configuration/SACSConfig.php';
include_once APPPATH .  'third_party/SACS-Php-master/soap/SessionCreateRequest.php';
include_once APPPATH .  'third_party/SACS-Php-master/soap/SessionCloseRequest.php';
include_once APPPATH .  'third_party/SACS-Php-master/soap/IgnoreTransactionRequest.php';
include_once APPPATH .  'third_party/SACS-Php-master/soap/XMLSerializer.php';


class SACSSoapClient {

    private $lastInFlow = false;
    private $actionName;
	private $sharedContext;

    public function __construct($actionName) {
        $this->actionName = $actionName;
		$this->sharedContext = new SharedContext();
    }

    public function doCall($request) {
		
		//log_message('debug',$request);
		
        if ($this->sharedContext->getResult("SECURITY") == null) {
            //error_log("SessionCreate");
            $securityCall = new SessionCreateRequest();
			$sessionCreateRS=$securityCall->executeRequest();
			
			//log_message('debug',print_r($sessionCreateRS,true));
			if(array_key_exists('Header',$sessionCreateRS))
			{
				if(array_key_exists('BinarySecurityToken',$sessionCreateRS['Header']['Security']))
				{
					$this->sharedContext->addResult("SECURITY", $sessionCreateRS['Header']['Security']['BinarySecurityToken']);
				}
			}
            
        }
        $sacsClient = new SACSClient();
        $result = $sacsClient->doCall($this->getMessageHeaderXml($this->actionName) . $this->createSecurityHeader(), $request, $this->actionName);
        if ($this->lastInFlow) {
          //  error_log("Ignore and close");
            $this->ignoreAndCloseSession($this->sharedContext->getResult("SECURITY"));
            $this->sharedContext->addResult("SECURITY", null);
        }
		//log_message('debug',$result);
        return $result;
    }

    private function ignoreAndCloseSession($security) {
/*         $it = new IgnoreTransactionRequest();
        $it->executeRequest($security); */
        $sc = new SessionCloseRequest();
        $sc->executeRequest($security);
    }

    private function createSecurityHeader() {
        $security = array("Security" => array(
                "_namespace" => "http://schemas.xmlsoap.org/ws/2002/12/secext",
				"_attributes" => array(
					"soap-env:mustUnderstand"=>"0",
					
				),
                "BinarySecurityToken" => array(
                    "_attributes" => array("EncodingType" => "Base64Binary", "valueType" => "String"),
                    "_value" => $this->sharedContext->getResult("SECURITY")
                )
            )
        );
        return XMLSerializer::generateValidXmlFromArray($security,'eb');
    }

    public static function createMessageHeader($actionString) {
        $messageHeaderXml = SACSSoapClient::getMessageHeaderXml($actionString);
        
        return $messageHeaderXml;
    }

    private static function getMessageHeaderXml($actionString) {
		$Timestamp = date('Y-m-d').'T'.date('H:i:s').'Z';
		$config = SACSConfig::getInstance();
		
        $messageHeader = array("MessageHeader" => array(
                "_namespace" => "http://www.ebxml.org/namespaces/messageHeader",
				"_attributes" => array(
					"soap-env:mustUnderstand"=>"1",
					"eb:version" => "1.0"
				),
                "From" => array("PartyId" => ""),
                "To" => array("PartyId" => ""),
                "CPAId" => $config->getSoapProperty("group"),
                "ConversationId" => "convId",
                "Service" => $actionString,
                "Action" => $actionString,
                "MessageData" => array(
                    "MessageId" => "1000",
                    "Timestamp" =>$Timestamp,
                   
                )
            )
        );
        return XMLSerializer::generateValidXmlFromArray($messageHeader,'eb');
    }

    public function setLastInFlow($lastInFlow) {
        $this->lastInFlow = $lastInFlow;
    }

}

class SACSClient {

    function doCall($headersXml, $body, $action) {
        //Data, connection, auth
        $config = SACSConfig::getInstance();
        $soapUrl = $config->getSoapProperty("environment");
        
        // xml post structure
        $xml_post_string = '<soap-env:Envelope xmlns:soap-env="http://schemas.xmlsoap.org/soap/envelope/" xmlns:eb="http://www.ebxml.org/namespaces/messageHeader" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:xsd="http://www.w3.org/1999/XMLSchema">'
                . '<soap-env:Header>'
                . $headersXml
                . '</soap-env:Header>'
                . '<soap-env:Body>'
                . $body
                . '</soap-env:Body>'
                . '</soap-env:Envelope>';

        $headers = array(
            "Content-type: text/xml;charset=\"utf-8\"",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
            "SOAPAction: " . $action,
            "Content-length: " . strlen($xml_post_string)
        );
		
		//error_log($action, 3, FCPATH."/uploads/scass-errors.log");
       // error_log($xml_post_string, 3, FCPATH."/uploads/scass-errors.log");
		//log_message('debug',$xml_post_string);
        // PHP cURL  for https connection with auth
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_URL, $soapUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
//            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
 
//A given cURL operation should only take
//30 seconds max.
curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string); // the SOAP request
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_VERBOSE, false);

        // converting
        $response = curl_exec($ch);
        curl_close($ch);
		
		//error_log($response, 3,FCPATH."/uploads/scass-errors.log");
		
		log_message('debug',$xml_post_string);
		log_message('debug',$response);
		
        return $response;
		//return $xml_post_string;
    }

}
