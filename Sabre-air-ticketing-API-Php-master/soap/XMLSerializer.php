<?php

class XMLSerializer {
    // functions adopted from http://www.sean-barton.co.uk/2009/03/turning-an-array-or-object-into-xml-using-php/
	private $namespace_prefix='';
	private $namespace_prefix_colon='';
    public static function generateValidXmlFromArray($array, $namespace_prefix='', $node_block='nodes', $node_name='node') {
		
        $xml = self::generateXmlFromArray($array, $namespace_prefix, $node_name);
        return $xml;
    }

    private static function generateXmlFromArray($array, $namespace_prefix='',$node_name) {
        $xml = '';
		$namespace_prefix_colon='';
		if($namespace_prefix != '')
		{
			$namespace_prefix_colon=':';
		}
		
        if ((is_array($array) || is_object($array))) {
            foreach ($array as $key=>$value) {
                if (is_numeric($key)) {
                    $key = $node_name;
                }
                if ($key != '_namespace' && $key != '_attributes' && $key != '@attributes' && $key != '_value' && $key != '_child') {
                    $xml .= '<' . $namespace_prefix.$namespace_prefix_colon.$key .self::generateAttributesFromArray($value)
                        .self::generateNamespace($value,$namespace_prefix)
                            . '>' 
                            . self::generateXmlFromArray($value,$namespace_prefix, $node_name) . '</' . $namespace_prefix.$namespace_prefix_colon.$key . '>';
                }
                if ($key == '_value') {
                    $xml = htmlspecialchars($value, ENT_QUOTES);
                }
				if ($key == '_child') {
                    $xml = $value;
                }
            }
        } else {
            $xml = htmlspecialchars($array, ENT_QUOTES);
        }

        return $xml;
    }
    
    private static function generateAttributesFromArray($array) {
        if (isset($array['_attributes']) && is_array($array['_attributes'])) {
            
            $attributes = ' ';
            foreach ($array['_attributes'] as $key=>$value) {
                $attributes .= $key.'="'.$value.'" ';
            }
            return $attributes;
        }
		else if(isset($array['@attributes']) && is_array($array['@attributes']))
		{
            
            $attributes = ' ';
            foreach ($array['@attributes'] as $key=>$value) {
                $attributes .= $key.'="'.$value.'" ';
            }
            return $attributes;			
		}
		else {
            return '';
        }
    }
    
    private static function generateNamespace($namespace,$namespace_prefix='') {
		$namespace_prefix_colon='';
		if($namespace_prefix != '')
		{
			$namespace_prefix_colon=':';
		}
        if (isset($namespace['_namespace']) && $namespace['_namespace']) {
            return ' xmlns'.$namespace_prefix_colon.$namespace_prefix.'="'.$namespace['_namespace'].'"';
        } else {
            return '';
        }
    }
	
	
	
	private static function removeNamespaceFromXML( $xml='' )
	{
		if(strcmp('',$xml)=== 0)
		{
			return $xml;
		}
		$sxe=new SimpleXMLElement($xml);
		$ns=$sxe->getNamespaces(true);
		
		// Because I know all of the the namespaces that will possibly appear in 
		// in the XML string I can just hard code them and check for 
		// them to remove them
		//$toRemove = ['eb', 'soap-env', 'wsse'];
		$toRemove = array_keys($ns);
		// This is part of a regex I will use to remove the namespace declaration from string
		$nameSpaceDefRegEx = '(\S+)=["\']?((?:.(?!["\']?\s+(?:\S+)=|[>"\']))+.)["\']?';

		// Cycle through each namespace and remove it from the XML string
	   foreach( $toRemove as $remove ) {
			// First remove the namespace from the opening of the tag
			$xml = str_replace('<' . $remove . ':', '<', $xml);
			// Now remove the namespace from the closing of the tag
			$xml = str_replace('</' . $remove . ':', '</', $xml);
			// This XML uses the name space with CommentText, so remove that too
			$xml = str_replace($remove . ':commentText', 'commentText', $xml);
			// Complete the pattern for RegEx to remove this namespace declaration
			$pattern = "/xmlns:{$remove}{$nameSpaceDefRegEx}/";
			// Remove the actual namespace declaration using the Pattern
			$xml = preg_replace($pattern, '', $xml, 1);
			
			$pattern = "/ {$remove}:/";
			// Remove the actual namespace declaration using the Pattern
			$xml = preg_replace($pattern, ' ', $xml, 1);
		}

		// Return sanitized and cleaned up XML with no namespaces
		return $xml;
	}
	
	public static function xmlToArray($xml = '')
	{
		 $xml = self::removeNamespaceFromXML($xml);
		$ob= simplexml_load_string($xml);
		
		$json  = json_encode($ob);
		
		$xml2array = json_decode($json, true);
		
		return $xml2array;
		
		/* $CI = &get_instance();
		$CI->load->helper('xml_to_array');
		return xmlstr_to_array($xml ); */

	}
}

class SabreXMLElementBuilder
{
	public static function generatePassengerTypeQuantityXml($PassengerType='ADT',$Quantity='1')
	{
		$xml ='';
			$PassengerTypeQuantity = array( "PassengerTypeQuantity" => array("_attributes" => array("Code" => $PassengerType, "Quantity" =>$Quantity)));
			$xml = XMLSerializer::generateValidXmlFromArray($PassengerTypeQuantity);
			return $xml;
	}
	
	public static function generateOriginDestinationInformationXml($Origin='',$Destination='',$DepartureDate='')
	{
			return '';
	}	
}

