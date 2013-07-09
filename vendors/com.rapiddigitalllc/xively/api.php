<?php 


/**
 * Xively API
 * @author Daniel Boorn - daniel.boorn@gmail.com
 * @copyright Daniel Boorn
 * @license Creative Commons Attribution-NonCommercial 3.0 Unported (CC BY-NC 3.0)
 * @namespace Xively
 * @note Class uses a JSON api_path.js file that defines the API resources (endpoints and paths). 
*/

namespace Xively;
require_once('exception.php');

/**
 * Xively API
 * @author Daniel Boorn - daniel.boorn@gmail.com
 * @copyright Daniel Boorn
 * @license Creative Commons Attribution-NonCommercial 3.0 Unported (CC BY-NC 3.0)
 * @namespace Xively
 * @note Class uses a JSON api_path.js file that defines the API resources (endpoints and paths).
 */
class API{
	

	public $debug = false;//enable to view chaining process in action
	public $paths;
	public $contentType = 'json';

	protected $pathIds = array();	
	protected $endpointId;
	protected $response;	
	protected $code;
	protected $settings = array(
		'apiKey' => 'your api key',
		'baseUrl' => 'https://api.xively.com/v2',
	);
	
	/**
	 * construct
	 * @param $key
	 * @return void
	 * @throws \Exception
	 */
	public function __construct($key=null){
		$this->settings['apiKey'] = !empty($key) ? $key : $this->settings['apiKey'];
		$this->loadApiPaths();
	}
	
	/**
	 * forge factory
	 * @param $key
	 * @return void
	 */
	public static function forge($key=null){
		$self = new self($key);
		return $self;
	}	
	
	/**
	 * deboug output
	 * @return void
	 */
	public function d($obj){
		if($this->debug) var_dump($obj);
	}
	
	/**
	 * magic method for building chainable api path with trigger to invoke api method
	 * @param string $name
	 * @param array $args
	 * @return $this
	 */
	public function __call($name, $args){
		$this->endpointId .= $this->endpointId ? "_{$name}" : $name;
		$this->d($this->endpointId);
		$this->d($args);
		if(isset($this->paths[$this->endpointId])){
			$r = $this->invoke($this->endpointId, $this->paths[$this->endpointId]['verb'],$this->paths[$this->endpointId]['path'],$this->pathIds,current($args));
			$this->reset();
			return $r;
		}
		if(count($args)>0 && gettype($args[0]) != "array" && gettype($args[0]) != "object"){
			$this->pathIds[] = array_shift($args);		
		}
		return $this;		
	}
	
	/**
	 * clear properties used by chain requests
	 * @return void
	 */
	public function reset(){
		$this->endpointId = null;
		$this->pathIds = array();
	}
	
	/**
	 * set content type to xml
	 */
	public function xml(){
		$this->contentType = 'xml';
		return $this;
	}
	
	/**
	 * set content type to json
	 */
	public function json(){
		$this->contentType = 'json';
		return $this;
	}
	
	/**
	 * set content type to csv
	 */
	public function csv(){
		$this->contentType = 'csv';
		return $this;
	}
	
	
	/**
	 * returns parsed path with ids (if any)
	 * @param string $path
	 * @param array $ids
	 * @return string
	 * @throws \Exception
	 */
	protected function parsePath($path, $ids){
		$parts = explode("/",ltrim($path,'/'));
		for($i=0; $i<count($parts); $i++){
			if(empty($parts[$i])) continue;
			if($parts[$i]{0}=="{"){
				if(count($ids)==0) throw new Exception("Api Endpont Path is Missing 1 or More IDs [path={$path}].");
				$parts[$i] = array_shift($ids);
			}
		}
		return '/'.implode("/",$parts);
	}
	
	
	/**
	 * fetch request form api
	 * @param string $verb
	 * @param string $path
	 * @param mixed $params
	 * @return array $result
	 */
	protected function fetch($verb, $path, $params){
		
		$headers = array("X-ApiKey: {$this->settings['apiKey']}");
		$url = "{$this->settings['baseUrl']}{$path}.{$this->contentType}";		
		$opts = array(
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HEADER => false,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CUSTOMREQUEST => $verb,
			CURLOPT_HTTPHEADER => $headers,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLINFO_HEADER_OUT => true,
		);
		if($verb == "POST" || $verb == "PUT"){
			$opts[CURLOPT_POSTFIELDS] = $this->contentType == "json" ? json_encode($params) : $params;
			$this->d($opts[CURLOPT_POSTFIELDS]);
		}else if($params){
			$url .= "?" . http_build_query($params);
		}
		$this->d($url);
		
		$ch = curl_init($url);		
		curl_setopt_array($ch, $opts);
		$response  = curl_exec($ch);		
		$code = curl_getinfo($ch,CURLINFO_HTTP_CODE);
		
		if($response===false || $code >= 400){		
			throw new Exception($response  ? $response  : curl_error($ch), $code, $response);
		}
		
		return array('response'=>$response, 'code'=>$code);
	}	
	
	/**
	 * invoke api endpoint method
	 * @param string $id
	 * @param string $verb
	 * @param string $path
	 * @param array $ids=null
	 * @param mixed $params=null
	 */
	public function invoke($id, $verb, $path, $ids=null, $params=null){
		$path = $this->parsePath($path, $ids);
		$this->d("Invoke[$id]: {$verb} {$path}",$params);
		$r = $this->fetch($verb, $path, $params);
		$this->response = $r['response'];
		$this->code = $r['code'];
		$this->d($this->response);
		return $this;
	}

	/**
	 * return api response
	 * @return object|boolean
	 */
	public function get(){
		if(empty($this->response) && empty($this->code)) throw new Exception('Nothing to get. Please trigger resource first');
		$r = $this->contentType == "json" ? json_decode($this->response) : $this->response;
		$r = !$r && $this->code == 200 ? true : $r;
		unset($this->response, $this->code);
		return $r;
	}
	
	/**
	 * loads api paths list from json file
	 * @return void
	 */
	protected function loadApiPaths(){
		$filename = __DIR__ . "/api_paths.json";
		$this->paths = json_decode(file_get_contents($filename),true);
	}
	
}
