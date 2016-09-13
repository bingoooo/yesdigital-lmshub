<?php
namespace FragTale\Controller\Yny_Reports\Export;
use FragTale\Controller\Yny_Reports\Export;

/**
 * @author fabrice
 */
class Xlsx extends Export{
	
	protected $dbinstancename;
	
	/**
	 * @var \PHPExcel
	 */
	protected $PHPXL;
	
	/**
	 * @var \PHPExcel_Worksheet
	 */
	protected $XlActiveSheet;
	
	protected $branchname;
	
	function initialize(){
		require_once LIB_ROOT.'/PHPExcel.php';
		$this->PHPXL = new \PHPExcel();
		
		parent::initialize();
		
		$this->dbinstancename = defined('DEVEL') ? 'ynytest' : 'ynynewlms';
		if (!empty($_REQUEST['instance'])) $this->dbinstancename = $_REQUEST['instance'];
		
		$this->_view->setCurrentScript(TPL_ROOT.'/views/yny_reports/export/xlsx.phtml');
		$this->_view->data = array();
	}
	
	function main(){
		
	}
	
	function getCurrentExcelFileName($branchname=null){
		if (empty($branchname)) $branchname = strtoupper($this->branchname);
		//The file is cached for 1 day
		$storageFolder = DOC_ROOT.'/storage';
		if (!file_exists($storageFolder))
			mkdir($storageFolder, 0775, true);
		$excelFolder = $storageFolder.'/excel';
		if (!file_exists($excelFolder))
			mkdir($excelFolder, 0775, true);
		$excelFileName = $excelFolder.'/'.$branchname.date('-Y-m-d').'.xlsx';
		return $excelFileName;
	}
	
	function cacheXlsx($branchname=null){
		$excelFileName = $this->getCurrentExcelFileName($branchname);
		if (!file_exists($excelFileName) || !empty($_REQUEST['refresh'])){
			# Using explicitly an Excel 2007 PHP writer object
			(new \PHPExcel_Writer_Excel2007($this->PHPXL))
				->setPreCalculateFormulas() // This is needed to force the PHP Excel spreadsheet to execute the formulas intending to view the result into the downloaded file
				->save($excelFileName);
		}
		return $excelFileName;
	}
	
	function sendXlsx($branchname=null){
		if (empty($branchname)) $branchname = strtoupper($this->branchname);
		$excelFileName	= $this->cacheXlsx($branchname);
		$excelContent	= file_get_contents($excelFileName);
		# Using headers for Excel2007
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="'.basename($excelFileName).'"');
		header('Cache-Control: max-age=0');
		// If you're serving to IE 9, then the following may be needed
		//header('Cache-Control: max-age=1');
			
		// If you're serving to IE over SSL, then the following may be needed
		//header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
		header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header ('Pragma: public'); // HTTP/1.0
		
		echo $excelContent;
		exit;
	}
	
	/**
	 * Using cached file if it is prod env
	 */
	function checkingCacheUse($branchname=null){
		if (empty($this->branchname)) return false;
		if (empty($branchname)) $branchname = strtoupper($this->branchname);
		if (empty($_REQUEST['debug']) && empty($_REQUEST['refresh']) && file_exists($this->getCurrentExcelFileName($branchname)))
			$this->sendXlsx($branchname);
	}
	
	function buildDataTree($dbdata){
		if (!empty($dbdata)){
			foreach ($dbdata as $i=>$row){
				if (empty($row['user_id'])) continue;
				$uid = $row['user_id'];
				if (!isset($this->_view->data[$uid])){
					foreach (array('login', 'firstname', 'lastname', 'email', 'recommended_level', 'acquired_level', 'country', 'branch_id', 'branch_name', 'parent_branch_id', 'parent_branch_name') as $field)
						$this->_view->data[$uid][$field] = isset($row[$field]) ? $row[$field] : null;
				}
				//if (!empty($row['path_id'])){
					$path_id = !empty($row['path_id']) ? $row['path_id'] : 'UNKNOWN';
					if (!isset($this->_view->data[$uid]['learning_plans'][$path_id])){
						foreach (array(
								'path_code',
								'path_name',
								'path_txt',
								'create_date',
								'img_url',
								'days_valid',
								'catch_up_enabled',
								'catch_up_limit',
								'enable_final_evaluation',
								'template_id',
								'template_name',
								'certificate_enabled',
								'certificate_name',
								'user_lp_completed',
								'user_lp_date_assign',
								'user_lp_date_begin_validity',
								'user_lp_date_end_validity',
								'user_lp_catchup_limit',
								'user_lp_timespent'
						) as $field)
							$this->_view->data[$uid]['learning_plans'][$path_id][$field] = isset($row[$field]) ? $row[$field] : null;
					}
					if (!empty($row['course_id'])){
						$course_id = (int)$row['course_id'];
						//if (!isset($this->_view->data[$uid]['learning_plans'][$path_id]['courses'][$course_id])){
							foreach (array(
									'course_code',
									'course_name',
									'course_txt',
									'course_image',
									'course_language',
									'course_status',
									'course_type',
									'course_sub_start_date',
									'course_sub_end_date',
									'course_date_begin',
									'course_date_end',
									'course_link',
									'course_category_name',
									'course_label',
									'course_certificate_enable',
									'course_certificate_name',
									'user_course_date_inscripted',
									'user_course_date_first_access',
									'user_course_date_last_access',
									'user_course_date_completed',
									'user_course_status',
									'user_course_waiting',
									'user_course_score',
									'user_course_timespent',
									'session_id',
									'session_date_begin',
									'session_date_end'
							) as $field){
								if (empty($this->_view->data[$uid]['learning_plans'][$path_id]['courses'][$course_id][$field]))
									$this->_view->data[$uid]['learning_plans'][$path_id]['courses'][$course_id][$field] = isset($row[$field]) ? $row[$field] : null;
							}
						//}
					}
				//}
			}
		}
		unset($dbdata);
	}
	
	function retrieveData($branchid){
		$branchIds = $this->fetchChildBranchids($branchid);
		$branchIds[] = $branchid;
		$query =
			'SELECT DISTINCT '.
				'V1.*, '.
				'UAI.recommended_level, UAI.acquired_level, UAI.country,'.
				'LPI.*, '.
				'V2.course_completed AS user_lp_completed, '.
				'V2.date_assign AS user_lp_date_assign, '.
				'V2.date_begin_validity AS user_lp_date_begin_validity, '.
				'V2.date_end_validity AS user_lp_date_end_validity, '.
				'V2.catchup_user_limit AS user_lp_catchup_limit, '.
				'V2.timespent AS user_lp_timespent '.
			'FROM V_USER_COURSES AS V1 '.
			'LEFT JOIN V_USER_LEARNINGPLAN_COURSES AS V2 ON V2.user_id = V1.user_id AND V2.course_id = V1.course_id '.
			'LEFT JOIN LearningPlanInfo LPI ON LPI.path_id = V2.path_id '.
			'INNER JOIN V_USER_ADD_INFOS UAI ON UAI.user_id = V1.user_id '.
 			'WHERE V1.branch_id IN ('.implode(',', $branchIds).') '.
 				'OR V1.parent_branch_id IN ('.implode(',', $branchIds).') '.
			'ORDER BY V1.lastname ASC , V1.firstname ASC , V1.course_id ASC;';
		unset($branchIds);
		return $this->getDb($this->dbinstancename)->getTable($query);
	}
	
	function fetchChildBranchids($parentbranchid){
		$branchids = $subbranchids = array();
		if ($result = $this->getDb($this->dbinstancename)->getTable("SELECT DISTINCT branch_id FROM BranchInfo WHERE parent_id = $parentbranchid")){
			foreach ($result as $row){
				$branchids[] = $row['branch_id'];
			}
			foreach ($branchids as $branchid){
				if ($result = $this->fetchChildBranchids($branchid))
					$subbranchids = array_merge($subbranchids, $result);
			}
		}
		return array_merge($branchids, $subbranchids);
	}
	
	/**
	 * @param string $strDate	"yyyy-mm-dd HH:ii:ss"
	 * @return mixed Excel date/time value or boolean FALSE on failure, null if date "0000-00-00"
	 */
	function toExcelDateFormat($strDate){
		return (stripos($strDate, '0000-00-00')!==false || empty($strDate)) ? null : \PHPExcel_Shared_Date::PHPToExcel(strtotime($strDate));
	}
}