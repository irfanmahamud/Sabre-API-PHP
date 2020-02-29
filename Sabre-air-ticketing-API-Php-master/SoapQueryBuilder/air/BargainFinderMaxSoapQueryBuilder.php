<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class BargainFinderMaxSoapQueryBuilder
{
	private static $QueryArray=array();
	
	public function __construct() {
        self::$QueryArray = array(
		"TripType"=>"OneWay",
		"OriginDestinationInformation"=>array(
			),
		"NumberOfAdult"=>"1",
		"NumberOfChild"=>"0",
		"NumberOfInfant"=>"0",
		"Currency" =>"BDT"
		);
    }
	
	public static function build()
	{
		return self::$QueryArray;
	}
	
	public static function SetOriginDestinationInformation($Origin='',$Destination='',$DepartureDate='')
	{
		if(strcmp($Origin,'')!==0 && strcmp($Destination,'')!==0 && strcmp($DepartureDate,'')!==0)
		{
			$DepartureDate = date('Y-m-d',strtotime($DepartureDate));
			self::$QueryArray["OriginDestinationInformation"][]=array(
				"Origin"=>$Origin,"Destination"=>$Destination,"DepartureDate"=>$DepartureDate
			);
		}

	}

	public static function SetTripType($TripType = '')
	{
		self::$QueryArray["TripType"] = $TripType;
	}
	
	public static function SetNumberOfAdult($NumberOfAdult = 1)
	{
		self::$QueryArray["NumberOfAdult"] = $NumberOfAdult;
	}
	
	public static function SetNumberOfChild($NumberOfChild = 0)
	{
		self::$QueryArray["NumberOfChild"] = $NumberOfChild;
	}
	
	public static function SetNumberOfInfant($NumberOfInfant = 0)
	{
		self::$QueryArray["NumberOfInfant"] = $NumberOfInfant;
	}

	public static function SetCurrency($Currency = 'BDT')
	{
		self::$QueryArray["Currency"] = strtoupper($Currency);
	}
	
	public static function SetClass($Class = 'economy')
	{
		
		self::$QueryArray["Class"] = ucfirst($Class);
	}	
}