<?php
namespace FragTale\YnY;
/**
 * 
 * @author fragbis
 *
 */
class Curl{
	
	protected static $doceboInstances = array(
		//Sandbox AFOSCHI
		'default'	=> array(
			'url'		=> 'http://afoschi-etime-git.docebo.info/api/',
			'api_key'	=> 't_!XDUubB_PlQdqvs55OQGwL',
			'api_secret'=> 'rgPBl*Lhv0tX4sqk-n2xCxw*r_CDnJU0CY-V'
		),
	);
	
	/**
	 * @param string				$method			The original method name given by the Docebo API such as "user/profile"
	 *  											@see https://doceboapi.docebosaas.com/api/docs
	 * @param array					$postParams		Parameters to pass into the Curl post call
	 * @param mixed|array|string	$instance		if is array: the curl connexion params. If string, the instance name
	 * @return array
	 */
	public static function call($method, $postParams=array(), $instance=''){
		//Find key, url and secret
		if (!empty($instance))
			$instance = 'default';
		else
			$clientHost = gethostbyaddr($_SERVER['REMOTE_ADDR']);
		if (isset(self::$doceboInstances[$clientHost])){
			if (empty(self::$doceboInstances[$clientHost]['url']))
				return array(
					'success'	=> 0,
					'message'	=> 'Development error: URL property not defined for "'.$clientHost.'" instance.'
				);
			else
				$curlParams = self::$doceboInstances[$clientHost];
		}
		else{
			if (empty(self::$doceboInstances['default']['url']))
				return array(
					'success'	=> 0,
					'message'	=> 'Development error: URL property not defined for "default" instance.'
				);
			else
				$curlParams = self::$doceboInstances['default'];
		}
		
		$url	= trim($curlParams['url'], '/').'/'.trim($method, '/');
		$sha1	= sha1(implode(",", $postParams) . "," . $curlParams['api_secret']);
		$code	= base64_encode($curlParams['api_key'] . ":" . $sha1);
		$result = null;
		
		try{
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_POST, count($postParams));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postParams));
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-Authorization: Docebo " . $code));
		
			$result = curl_exec($ch);
		}
		catch(\Exception $exc){
			return array(
				'success'	=> 0,
				'message'	=> 'Exception code '.$exc->getCode().': '.$exc->getMessage()
			);
		}
		
		if (false === $result)
			return array(
				'success'	=> 0,
				'message'	=> 'Curl error #' . curl_errno($ch) . ': ' . curl_error($ch)
			);
		else
			return json_decode($result, true);
	}
	
	/**
	 * Perform API call
	 *
	 * @param string $url       The URL address of the API method to call
	 * @param array $postParams An array of parameters to POST to the API
	 *
	 * @return bool|mixed The resulting JSON string, or FALSE on error
	function call(){
		//Check arguments
		if (empty($_REQUEST['id_user']) || empty($_REQUEST['id_learningplan']))
			return false;
		$postParams = array(
			'id_user'         => $_REQUEST['id_user'],
			'id_learningplan' => $_REQUEST['id_learningplan']
		);
		//Find key, url and secret
		$clientHost = gethostbyaddr($_SERVER['REMOTE_ADDR']);
		if (isset(self::$doceboInstances[$clientHost]))
			$curlParams = self::$doceboInstances[$clientHost];
		else
			$curlParams = self::$doceboInstances['default'];
		
		$sha1	= sha1(implode(",", $postParams) . "," . $curlParams['api_secret']);
		$code	= base64_encode($curlParams['api_key'] . ":" . $sha1);
		
		$theHeaders = &$this->headers;
	
		$ch = curl_init($host . $url);
		curl_setopt($ch, CURLOPT_VERBOSE, true);
		$verb = fopen('php://temp', 'w+');
		curl_setopt($ch, CURLOPT_POST, count($postParams));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(
				$ch,
				CURLOPT_HEADERFUNCTION,
				function ($curl, $header) use (&$theHeaders) {
					// echo "adding header $header";
					$length       = strlen($header);
					$theHeaders[] = $header;
					return $length;
				}
				);
		curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 15);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postParams));
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-Authorization: Docebo " . $code));
	
		$json = curl_exec($ch);
		if (false === $json) {
			rewind($verb);
			exit('Verbose CURL info: <pre>' . htmlspecialchars(stream_get_contents($verb)));
		}
	
		$http = explode(" ", $this->headers[0]);
		if ((int)$http[1] >= 300) {
			$this->log(sprintf("An error occurred with this request: (%s) %s", $http[1], $http[2]));
			return false;
		};
		$this->log("  HTTP Status: " . $http[1]);
		$this->log(sprintf("  Received data: %s", $json));
		return $json;
	}
	 */

}