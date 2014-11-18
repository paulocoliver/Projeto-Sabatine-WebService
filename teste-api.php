<?php 
class cURL{
	
	private $host;
	private $resource;
	private $method;
	private $autentication;
	private $params;
	private $key;
	
	function __construct(){
		$this->host 	= 'http://www.webservice-sabatine.dev';
		$this->key  	= 'd2UyM3dlMjN3ZTIz';
		$this->method  	= 'GET';
	}	
	
	public function set($key, $value){
		$this->$key = $value;
		return $this;
	}
	
	public function get($key){
		return $this->$key;
	}
	
	public function exec(){
		
		$ch = curl_init($this->host.(!empty($this->resource) ? $this->resource : ''));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->method);
		if(!empty($this->autentication)){
			curl_setopt($ch, CURLOPT_USERPWD, "{$this->autentication['user']}:{$this->autentication['password']}"); 
		}
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"x-api-key : {$this->key}"
		));
		if(!empty($this->params)){
			curl_setopt($ch, CURLOPT_POSTFIELDS, $this->params);
		}
		
		$data = curl_exec($ch); 
		if (curl_errno($ch)) { 
			printf(htmlspecialchars(curl_error($ch)));
		} else { 
			// Show me the result 
			echo '<pre>';
				print_r($data);
			echo '</pre>';
		} 
		curl_close($ch); 
	}
}

$cURL = new cURL();
$cURL->set('resource', '/categoria')
	 ->set('method', 'DELETE')
	 ->set('autentication', array('user' => 'ro.damasceno@gmail.com', 'password' => '123456'))
	->set('params', json_encode(array('id' => '27')))
	 ->exec();
?>