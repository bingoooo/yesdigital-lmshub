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
		'provalliance'=>array(
			'url'		=> 'https://www.provalliancelearning.com/api/',
			'api_key'	=> 'F8!uTj!YlIN!*XXYvr_%tNrH',
			'api_secret'=> 'OLeR0azrO8si%CJYvzlw_itZElz57R_NVczm'
		),
		'yny'		=> array(
			'url'		=> 'https://www.yesnyoulearning.com/api/',
			'api_key'	=> '3KCJNd4iJVi*h#-G6K_r%EB-',
			'api_secret'=> 'bE%foALNF0Xwqa*9SwQteSwsyHkZTU_*07#i'
		),
		//Sandbox Provo ?
		'fsirocco'	=> array(
			'url'		=> 'http://fsirocco-etime-git.docebo.info/api/',
			'api_key'	=> 't_!XDUubB_PlQdqvs55OQGwL',
			'api_secret'=> 'rgPBl*Lhv0tX4sqk-n2xCxw*r_CDnJU0CY-V'
		)
	);
	
	protected static $allowedMethod = array(
			'yny_learningplan/getEvaluationData',
			'iltsessions/listAction',	//Those 2...
			'yny_session_api/list',		//...are similar but do not pass the exact same parameters
			'yny_session_api/listUsers',//List all users in a given session an course
			'yny_user_api/userCourses',
			'user/userCourses',
			'user/authenticate',
			'user/logout',
			'user/profile',
			'user/lostpassword',
			'organization/listObjects',
			'organization/play',
			'yny_user/messages',
			'yny_user/notifications',
	);
	
	/**
	 * @param string				$method			The original method name given by the Docebo API such as "user/profile"
	 *  											@see https://doceboapi.docebosaas.com/api/docs
	 * @param array					$postParams		Parameters to pass into the Curl post call
	 * @param mixed|array|string	$instance		if is array: the curl connexion params. If string, the instance name
	 * @return array
	 */
	public static function call($method, $postParams=array(), $instance=''){
		//Check the allowed method
		if (!in_array(trim($method, '/'), self::$allowedMethod)){
			return array(
				'success'	=> 0,
				'message'	=> $method.': unknown or restricted method'
			);
		}
		//Find key, url and secret
		if (!empty($instance['url']) && !empty($instance['api_key']) && !empty($instance['api_secret']))
			$curlParams = $instance;
		else{
			if (empty($instance))
				$instance = 'default';
			if (isset(self::$doceboInstances[$instance])){
				if (empty(self::$doceboInstances[$instance]['url']))
					return array(
							'success'	=> 0,
							'message'	=> 'Development error: URL property not defined for "'.$instance.'" instance.'
					);
				else
					$curlParams = self::$doceboInstances[$instance];
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
}