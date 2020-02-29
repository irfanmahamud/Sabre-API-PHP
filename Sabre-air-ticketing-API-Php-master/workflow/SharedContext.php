<?php
class SharedContext {

    private $results;
    
	protected $CI;
	
    public function __construct() {
        $this->results["SECURITY"] = null;
		$this->CI=&get_instance();
    }
    
    public function addResult($key, $result) {
        $this->results[$key] =  $result;
		
		$this->CI->session->set_tempdata($key, $result, 780);
    }
    
    public function getResult($key) {
		return $this->CI->session->tempdata($key);
       // return $this->results[$key];
    }
}
