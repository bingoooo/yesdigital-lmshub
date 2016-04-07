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
	
	function doPostBack(){
		//Nothing to code. Preventing parent behavior.
	}
	
	function main(){
		$requestData = $this->getRequestData(true, (defined('ENV') && ENV==='devel'), false);
		if (empty($requestData['id_user']))
			$this->exitOnError(417, 'Expectation failed');
		$id_user = (int)$requestData['id_user'];
		$id_course = null;
		if (!empty($requestData['project'])){
			$project = trim(strtolower($requestData['project']));
			if (!empty($this->mapProjectToCourse[$project]))
				$id_course = $this->mapProjectToCourse[$project];
		}
		if (!$id_course && !empty($requestData['id_course']))
			$id_course = (int)$_POST['id_course'];
		if (!$id_course)
			$this->exitOnError(417, 'Expectation failed');
		$referer = !empty($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : 'PROVALLIANCE';
		$_SESSION[$referer]['id_user'] = $id_user;
		$jsonfile = $this->getJsonCourseFilepath($id_course, !empty($requestData['refresh']));
		if ($jsonfile && file_exists($jsonfile))
			$this->_view->json = file_get_contents($jsonfile);
		else
			$this->exitOnError(500, 'JSON file not found');
	}
	
	/**
	 * Create the JSON file if not exists
	 * @param integer $id_course	The course ID
	 * @param boolean $refresh		Force JSON file creation
	 * @return boolean|string
	 */
	function getJsonCourseFilepath($id_course, $refresh=false){
		if (empty($id_course)) return false;
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
		$filepath = $path.'/'.$id_course.'.json';
		//Refresh file if: file not exist, argument "$refresh" set to true, the file was created yesterday
		if (!file_exists($filepath) || $refresh || date('Ymd', filemtime($filepath)) < date('Ymd')){
			$courses = $this->retrieve('organization/listObjects', array('id_course'=>$id_course));
			if (!empty($courses['objects']))
				if (!file_put_contents($filepath, json_encode($courses['objects'])))
					$this->exitOnError(500, 'Unabled to create file '.$filepath);
		}
		return $filepath;
	}
}