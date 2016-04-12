<?php
namespace FragTale\Controller\Provalliance_Json;
use FragTale\Controller\Provalliance_Json;

/**
 * @author fabrice
 */
class Course_Structure extends Provalliance_Json{
	
	/**
	 * @desc Key: project name, Value: course ID
	 * @var array
	 */
	protected $mapProjectToCourse = array(
		'cco'		=>405,
		'intermede'	=>396,
		'jld'		=>480,
		'fprovost'	=>471,
		'fabiosalsa'=>567
	);
	
	protected $id_user;
	protected $id_course;
	
	function doPostBack(){
		//Nothing to code. Preventing parent behavior.
	}
	
	function main(){
		$requestData = $this->getRequestData(true, (defined('ENV') && ENV==='devel'), false);
		if (empty($requestData['id_user']))
			$this->exitOnError(417, 'Expectation failed');
		$this->id_user	= (int)$requestData['id_user'];
		$this->id_course= null;
		if (!empty($requestData['project'])){
			$project = trim(strtolower($requestData['project']));
			if (!empty($this->mapProjectToCourse[$project]))
				$this->id_course = $this->mapProjectToCourse[$project];
		}
		if (!$this->id_course && !empty($requestData['id_course']))
			$this->id_course = (int)$_POST['id_course'];
		if (!$this->id_course)
			$this->exitOnError(417, 'Expectation failed');
		$referer = !empty($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : 'PROVALLIANCE';
		$_SESSION[$referer]['id_user'] = $this->id_user;
		$jsonfile = $this->getJsonCourseFilepath(!empty($requestData['full']), !empty($requestData['refresh']));
		if ($jsonfile && file_exists($jsonfile))
			$this->_view->json = file_get_contents($jsonfile);
		else
			$this->exitOnError(500, 'JSON file not found '.$jsonfile);
	}
	
	/**
	 * Create the JSON file if not exists
	 * @param boolean $withLaunchUrl	Force build full data
	 * @param boolean $refresh			Force JSON file creation
	 * @return boolean|string
	 */
	function getJsonCourseFilepath($withLaunchUrl=false, $refresh=false){
		foreach (array(
				DOC_ROOT.'/storage',
				DOC_ROOT.'/storage/provalliance',
				DOC_ROOT.'/storage/provalliance/json',
				DOC_ROOT.'/storage/provalliance/json/course_structures')
			as $path){
			if (!file_exists($path)){
				if (!mkdir($path, 0775, true)){
					$this->exitOnError(500, 'Unabled to create directory '.$path);
				}
			}
		}
		if ($withLaunchUrl)
			$filepath = $path.'/full_'.$this->id_course.'.json';
		else
			$filepath = $path.'/'.$this->id_course.'.json';
		//Refresh file if: file not exist, argument "$refresh" set to true, the file was created yesterday
		if (!file_exists($filepath) || $refresh || date('Ymd', filemtime($filepath)) < date('Ymd')){
			$courses = $this->retrieve('organization/listObjects', array('id_course'=>$this->id_course));
			if (!empty($courses['objects'])){
				if ($withLaunchUrl){
					$objects = $courses['objects'];
					foreach ($objects as $ix => $object){
						if (empty($object['id_org'])) continue;
						## Get launch_url for this quiz
						$launchurl = $this->retrieve('organization/play', array('id_user'=>$this->id_user, 'id_org'=>$object['id_org']));
						if (!empty($launchurl['launch_url'])){
							$launch_url = str_replace(
									'fasle', 'false',
									str_replace(
											'id_user='.$this->id_user, 'id_user=%id_user%',
											$launchurl['launch_url']
											)
									);
							if ($authpos = strpos($launch_url, 'auth_code=')){
								$launch_url = substr($launch_url, 0, $authpos).'auth_code=%auth_code%'.substr($launch_url, $authpos+46);
							}
							$courses['objects'][$ix]['launch_url'] = $launch_url;
						}
						else{
							$courses['objects'][$ix]['launch_url'] = '#';
						}
					}
				}
				if (!file_put_contents($filepath, json_encode($courses['objects'])))
					$this->exitOnError(500, 'Unabled to create file '.$filepath);
			}
		}
		return $filepath;
	}
}