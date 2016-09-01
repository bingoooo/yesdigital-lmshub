<?php
namespace FragTale\Controller\Yny_Reports\Export\Xlsx;
use FragTale\Controller\Yny_Reports\Export\Xlsx;

/**
 * @author fabrice & benjamin
 */
class Generic extends Xlsx{
	
	private $tree = array();
	
	function main(){
		//Retrieving and sorting data
		// TODO : Sorting search query with a $_REQUEST['account'] or else
		$PMCode = isset($_REQUEST['pm'])?$_REQUEST['pm']:439;
		$this->buildDataTree($this->retrieveGeneric($PMCode));
		$BITable = $this->_view->branch;
		$BTTable = $this->_view->translations;
		// echo 'retrieved : '.count($this->tree).'<br>';
		
		//Building Excel file
		if (!empty($this->_view->data) && empty($_REQUEST['debug'])){
			$this->PHPXL = \PHPExcel_IOFactory::load(TPL_ROOT.'/xlsx/generic.xlsx');
			$this->XlActiveSheet = $this->PHPXL->setActiveSheetIndex(0);
			$line = 2;
			foreach ($this->_view->data as $uid=>$User){
				// WIP : retrieve branch infos
				//$User['account'] = $this->getBranchPath($User['branch_id'], $BITable);
				$User['account'] = $this->getParentName($User['branch_id'], $BITable, $BTTable);
				$User['contract'] = $this->getBranchTranslation($User['branch_id'], $BTTable);
				// echo $User['account'].'<br>';
				/*if($User['branch_id'] == 190){
					echo $User['branch_name'].'<br>';
				}*/
				if (empty($User['learning_plans'])) continue;
				foreach ($User['learning_plans'] as $path_id=>$LP){
					$line++;
					$lpstartdate= (stripos($LP['user_lp_date_begin_validity'], '0000-00-00')!==false || empty($LP['user_lp_date_begin_validity'])) ? null : \PHPExcel_Shared_Date::PHPToExcel(strtotime($LP['user_lp_date_begin_validity'])); 
					$lpenddate	= (stripos($LP['user_lp_date_end_validity'], '0000-00-00')!==false || empty($LP['user_lp_date_end_validity'])) ? null : \PHPExcel_Shared_Date::PHPToExcel(strtotime($LP['user_lp_date_end_validity'])); 
					$this->XlActiveSheet
						->setCellValue('A'.$line, $User['account'])// TODO : Account
						->setCellValue('B'.$line, strtoupper($User['contract']))// TODO : Contract
						->setCellValue('C'.$line, !empty($User['lastname']) ? strtoupper($User['lastname']) : trim($User['login'], '/'))
						->setCellValue('D'.$line, strtoupper($User['firstname']))
						->setCellValue('E'.$line, $User['recommended_level'])//Starting level
						->setCellValue('F'.$line, $User['acquired_level'])//Current level
						->setCellValue('G'.$line, $LP['path_name'])//Booked program
						->setCellValue('H'.$line, $lpstartdate)
						->setCellValue('I'.$line, $lpenddate)
						// ->setCellValue('J'.$line, strtoupper($User['branch_name']))//Branch																					
						->setCellValue('K'.$line, $lpstartdate)
						// ->setCellValue('L'.$line, $lpenddate)
						;
					
					$elearnings = $microlearnings = $sessions = $catch_up = $esp = $business_keys = array();
					$globalTime = $iCol = $nbElearnings = $nbElCompleted = $nbSessionPassed = 0;
					$comments = '';//'TODO'; // Add a commentary column
					
					$lastAccess = null;
					$courseColumns = array('O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
					//Get courses					
					if (!empty($LP['courses'])){
						foreach ($LP['courses'] as $course_id=>$Course){
							if($Course['user_course_date_last_access'] !== '0000-00-00 00:00:00'){
								$lastAccess = $Course['user_course_date_last_access'];
							}
								
							if(stripos($Course['course_code'], 'ESP')!==false){
								$esp[$course_id] = $Course;
							}
							if (stripos($Course['course_code'], 'CATCH')!==false){
								$catch_up[$course_id] = $Course;
							}
							if(stripos($Course['course_code'], 'SKS')!==false){
								$sessions[$course_id] = $Course;
							}
							if (stripos($Course['course_code'], 'BK')!==false || stripos($Course['course_label'], 'business keys')!==false){
								$business_keys[$course_id] = $Course;
							}
							
							if ($Course['course_type']==='elearning') {
								if (stripos($Course['course_name'], 'micro') !== false)
									$microlearnings[$course_id] = $Course;
									else{
										$Course['module_status'] = (!empty($Course['user_course_date_completed']) && stripos($Course['user_course_date_completed'], '0000-00-00')===false) ? 'Completed' :
										(empty($Course['user_course_date_first_access']) || stripos($Course['user_course_date_first_access'], '0000-00-00')!==false ? 'Not started' : 'In progress');
										if ($Course['module_status']==='Completed')
											$nbElCompleted++;
											$elearnings[$course_id] = $Course;
									}
							}
						}
					}
					
					$total_time = 0;
					
					// Insert entries into the xlsx file
					if (!empty($elearnings)){
						$el_done = 0;
						$el_count = count($elearnings);
						$el_time = 0;
						foreach ($elearnings as $course_id => $Course){
							$el_time += $Course['user_course_timespent'];
							if($Course['user_course_status'] == 2)
								$el_done += 1;
						}
						$this->XlActiveSheet
							->setCellValue('J'.$line, $el_done.'/'.$el_count)
							->setCellValue('K'.$line, $el_time/86400, \PHPExcel_Cell_DataType::TYPE_NUMERIC)
						;
						$total_time += $el_time;
					}
					if (!empty($sessions)){
						$sessions_done = 0;
						$sessions_count = count($sessions);
						$sessions_time = 0;
						foreach ($sessions as $course_id => $Course){
							if($Course['user_course_status'] == 2){
								$sessions_done += 1;
								if (!empty($Course['session_date_end'])){
									$session_time = strtotime($Course['session_date_end']) - strtotime($Course['session_date_begin']);
									$sessions_time += $session_time;
								}
							}
						}
						$this->XlActiveSheet
							->setCellValue('L'.$line, $sessions_done.'/'.$sessions_count)
							->setCellValue('M'.$line, $sessions_time/86400, \PHPExcel_Cell_DataType::TYPE_NUMERIC)
						;
						$total_time += $sessions_time;
					}
					
					if (!empty($catch_up)){
						$cu_done = 0;
						$cu_count = count($catch_up);
						$cu_time = 0;
						foreach ($catch_up as $course_id => $Course){
							$cu_time += $Course['user_course_timespent'];
							if($Course['user_course_status'] == 2) $cu_done += 1;
						}
						$this->XlActiveSheet
							->setCellValue('N'.$line, $cu_done.'/'.$cu_count)
							->setCellValue('O'.$line, $cu_time/86400, \PHPExcel_Cell_DataType::TYPE_NUMERIC)
						;
						$total_time += $cu_time;
					}
					
					if (!empty($microlearnings)){
						$ml_time = 0;
						foreach ($microlearnings as $course_id => $Course){
							$ml_time += $Course['user_course_timespent'];
						}
						$this->XlActiveSheet
							->setCellValue('P'.$line, ($ml_time/86400), \PHPExcel_Cell_DataType::TYPE_NUMERIC)
						;
						$total_time += $ml_time;
					}
					
					if(!empty($esp)){
						$esp_done = 0;
						$esp_count = count($esp);
						$esp_time = 0;
						foreach ($esp as $course_id => $Course){
							$esp_time += $Course['user_course_timespent'];
							if($Course['user_course_status'] == 2) $esp_done += 1;
						}
						$this->XlActiveSheet
							->setCellValue('Q'.$line, $esp_done.'/'.$esp_count)
							->setCellValue('R'.$line, ($esp_time/86400), \PHPExcel_Cell_DataType::TYPE_NUMERIC)
						;
						$total_time += $esp_time;
					}
					
					if(!empty($business_keys)){
						$business_keys_done = 0;
						$business_keys_count = count($business_keys);
						$business_keys_time = 0;
						foreach ($business_keys as $course_id => $Course){
							$business_keys_time += $Course['user_course_timespent'];
							if($Course['user_course_status'] == 2) $business_keys_done += 1;
						}
						$this->XlActiveSheet
							->setCellValue('S'.$line, $business_keys_done.'/'.$business_keys_count)
							->setCellValue('T'.$line, ($business_keys_time/86400), \PHPExcel_Cell_DataType::TYPE_NUMERIC)
						;
						$total_time += $business_keys_time;
					}
					
					//Total time
					$this->XlActiveSheet->setCellValue('V'.$line, ($total_time/86400), \PHPExcel_Cell_DataType::TYPE_NUMERIC);
								
					//$completion
					$lastAccess = (stripos($lastAccess, '0000-00-00')!==false || empty($lastAccess)) ? null : \PHPExcel_Shared_Date::PHPToExcel(strtotime($lastAccess)); 
					$this->XlActiveSheet
						->setCellValue('U'.$line, $comments)//Comments
						->setCellValue('W'.$line, $lastAccess)
					;
				}
			}
			$PM = "";
			foreach ($this->_view->branch as $key => $values){
				if($values['branch_id'] == $PMCode){
					$PM = strtoupper($values['branch_name']);
					break;
				}
			}
			$this->setExcelFinalFormat($line);
			$this->sendXlsx('Generic-'.$PM);
		}
	}
	
	function retrieveGeneric($code){
		// TODO : Generic retrieve of DB entries
		$branches = 'SELECT * FROM BranchInfo;';
		$this->_view->branch = $this->getDb($this->dbinstancename)->getTable($branches);
		$branchesTranslations = 'SELECT * FROM BranchTranslations WHERE language="french"';
		$this->_view->translations = $this->getDb($this->dbinstancename)->getTable($branchesTranslations);
		$BITable = $this->_view->branch;
		$this->getTreeFromPM($code, $BITable);
		$query =
			'SELECT DISTINCT '.
			'V1.*, '.
			'LPI.*, '.
			'V2.course_completed AS user_lp_completed, '.
			'V2.date_assign AS user_lp_date_assign, '.
			'V2.date_begin_validity AS user_lp_date_begin_validity, '.
			'V2.date_end_validity AS user_lp_date_end_validity, '.
			'V2.catchup_user_limit AS user_lp_catchup_limit, '.
			'V2.timespent AS user_lp_timespent, '.
			'ILT.session_id, '.
			'ILT.date_begin AS session_date_begin, '.
			'ILT.date_end AS session_date_end '.
			'FROM V_USER_COURSES AS V1 '.
			'LEFT JOIN V_USER_LEARNINGPLAN_COURSES AS V2 ON V2.user_id = V1.user_id AND V2.course_id = V1.course_id '.
			'LEFT JOIN LearningPlanInfo LPI ON LPI.path_id = V2.path_id '.
			'LEFT JOIN UserIltSessions AS UIS ON UIS.user_id = V1.user_id '.
			'LEFT JOIN IltSessionInfo AS ILT ON ILT.session_id = UIS.session_id AND ILT.course_id = V1.course_id '.
			'WHERE V1.branch_id IN ('.implode(',', $this->tree).') '.
			'ORDER BY V1.lastname ASC , V1.firstname ASC , V1.course_id ASC';
		//if($_REQUEST['debug']==true) $query .= ' LIMIT 2000';
		$query .= ';';
		// var_dump($this->_view->branchUsers);
		return $this->getDb($this->dbinstancename)->getTable($query);
	}
	
	function getTreeFromPM($branchId, $tree, $table = array()){
		$count = 0;
		$id = 0;
			foreach($tree as $index => $datas){
				if ($datas['parent_id'] == $branchId){
					$count++;
					$id = $datas['branch_id'];
					$this->getTreeFromPM($datas['branch_id'], $tree);
					if ($count > 0 && !in_array($datas['branch_id'], $this->tree)){
						array_push($this->tree, $id);
					}
				}
			}
	}
	
	function getParentName($branchId, $branches, $translations){
		$result = 'In dev';
		$parentId = null;
		foreach ($branches as $branch){
			if ($branch['branch_id'] == $branchId){
				$parentId = $branch['parent_id'];
				break;
			}
		}
		$result = $this->getBranchTranslation($parentId, $translations);
		return $result;
	}
	
	function getBranchTranslation($id, $translations){
		$translate = 'Translation Not Found';
		foreach($translations as $translation){
			if($translation['branch_id'] == $id){
				$translate = $translation['branch_name'];
				break;
			}
		}
		return $translate;
	}
	
	function getBranchPath($branchId, $table, $path = ""){
		$parentId = 0;
		foreach ($table as $key => $datas){
			if($datas['branch_id'] == $branchId){
				$parentId = $datas['parent_id'];
				$path = $datas['branch_name'].'|'.$path;
				break;
			}
		}
		if($parentId == 0){
			return $path;
		} else {
			return $this->getBranchPath($parentId, $table, $path);
		}
	}
	
	function setExcelFinalFormat($finalrowindex){
		//Set center alignment for columns
		foreach (array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V') AS $aCol){
			$this->XlActiveSheet->getStyle($aCol.'2:'.$aCol.$finalrowindex)
			->getAlignment()
			->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		}
		//Set date format
		foreach (array('H', 'I', 'W') as $aCol){
			$this->XlActiveSheet->getStyle($aCol.'2:'.$aCol.$finalrowindex)
			->getNumberFormat()
			->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_DATE_DDMMYYYY);
		}
		//Set time formats
		foreach (array('K', 'M', 'O', 'P', 'R', 'T', 'V') as $aCol){
			$this->XlActiveSheet
				->getStyle($aCol.'2:'.$aCol.$finalrowindex)
				->getNumberFormat()->setFormatCode('[HH]:MM:SS')
			;
		}
	}
	
}