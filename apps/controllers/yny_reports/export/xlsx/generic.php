<?php
namespace FragTale\Controller\Yny_Reports\Export\Xlsx;
use FragTale\Controller\Yny_Reports\Export\Xlsx;

/**
 * @author fabrice & benjamin
 */
class Generic extends Xlsx{
	
	protected $branchname = 'Generic';
	
	function main(){
		
		$PMCode = isset($_REQUEST['pm'])?$_REQUEST['pm']:439;
		
		//Finding PM branch name
		$branch_name = $this->getDb($this->dbinstancename)->getScalar('SELECT branch_name FROM V_FR_BRANCHES WHERE branch_id = '.$PMCode);
		if (empty($branch_name)){
			die('Unknown branch id '.$PMCode);
		}
		$branchname = $this->branchname.'-'.$branch_name;
		$this->checkingCacheUse($branchname);
		
		//Retrieving and sorting data
		$this->buildDataTree($this->retrieveGeneric($PMCode));
		
		# Building Excel file
		if (!empty($this->_view->data) && empty($_REQUEST['debug'])){
			// Sorting on "account name", "contract number" and "user names"
			$accounts = array();
			foreach ($this->_view->data as $uid=>$User){
				// WIP : retrieve branch infos
				$accounts[$User['parent_branch_name'].' - '.$User['branch_name']][$User['lastname'].' - '.$User['firstname'].' - '.$uid] = $User;

			}
			ksort($accounts);
			
			$this->PHPXL = \PHPExcel_IOFactory::load(TPL_ROOT.'/xlsx/generic.xlsx');
			$this->XlActiveSheet = $this->PHPXL->setActiveSheetIndex(0);
			$line = 2;
			foreach ($accounts as $Users){
				foreach ($Users as $User){
					if (empty($User['learning_plans'])) continue;
					foreach ($User['learning_plans'] as $path_id=>$LP){
						$isESP = false;
						$elearnings = $microlearnings = $sessions = $catch_up = $business_keys = $esp = array();
						$globalTime = $iCol = $nbElearnings = $nbElCompleted = $nbSessionPassed = 0;
						$comments = '';//'TODO'; // Add a commentary column
						if ($path_id==='UNKNOWN'){
							# Specific for ESP
							if (empty($LP['courses'])) continue;
							foreach ($LP['courses'] as $course_id=>$Course){
								//The ESP need to be displayed only once in the first LP found
								if(stripos($Course['course_code'], 'ESP')!==false){
									$esp[$course_id] = $Course;
									continue;
								}
							}
							if (empty($esp)) continue;
							$isESP = true;
						}
						$line++;
						$startDate = (!empty($LP['user_lp_date_begin_validity']) || stripos($LP['user_lp_date_begin_validity'], '0000-00-00')!==false) ? $LP['user_lp_date_begin_validity'] : $LP['user_lp_date_assign'];
						$this->XlActiveSheet
							->setCellValue('A'.$line, $User['parent_branch_name'])				// Account
							->setCellValue('B'.$line, strtoupper($User['branch_name']))// Contract
							->setCellValue('C'.$line, !empty($User['lastname']) ? strtoupper($User['lastname']) : trim($User['login'], '/'))
							->setCellValue('D'.$line, strtoupper($User['firstname']))
							->setCellValue('E'.$line, $User['acquired_level'])
							->setCellValue('F'.$line, $User['recommended_level'])
							->setCellValue('G'.$line, $isESP?'ESP':$LP['path_name'])//Booked program
							->setCellValue('H'.$line, $this->toExcelDateFormat($startDate))	// Start date
							->setCellValue('I'.$line, $this->toExcelDateFormat($LP['user_lp_date_end_validity']))	// End date
							// ->setCellValue('J'.$line, strtoupper($User['branch_name']))//Branch																					
							;
						
						$lastAccess = null;
						$courseColumns = array('O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
						//Get courses					
						if (!empty($LP['courses'])){
							foreach ($LP['courses'] as $course_id=>$Course){
								if($Course['user_course_date_last_access'] !== '0000-00-00 00:00:00' && $Course['user_course_date_last_access'] > $lastAccess){
									$lastAccess = $Course['user_course_date_last_access'];
								}
								
								if(stripos($Course['course_code'], 'SKS')!==false){
									$sessions[$course_id] = $Course;
								}
								elseif (stripos($Course['course_code'], 'BK')!==false || stripos($Course['course_label'], 'business keys')!==false){
									$business_keys[$course_id] = $Course;
								}
								elseif ($Course['course_type']==='elearning') {
									if (stripos($Course['course_name'], 'micro') !== false)
										$microlearnings[$course_id] = $Course;
									else{
										if ($Course['user_course_status']==2)
											$nbElCompleted++;
										$elearnings[$course_id] = $Course;
									}
								}
							}
							
							/** Specific case on CATCHUPS because it not depends on a Learning Plan: Must search into "UNKNOWN" Learning Plan Courses array **/
							if (!empty($User['learning_plans']['UNKNOWN']['courses'])){
								foreach ($User['learning_plans']['UNKNOWN']['courses'] as $course_id=>$Course){
									$catchpos = stripos($Course['course_code'], 'CATCH');
									if (!empty($sessions) && $catchpos!==false){
										// We have to match if this catchup match a corresponding SKS in this Learning plan
										$exp2match = trim(substr($Course['course_code'], 0, $catchpos), '_');
										foreach ($sessions as $session){
											if (stripos($session['course_code'], $exp2match)!==false)
												$catch_up[$course_id] = $Course;
										}
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
								->setCellValue('N'.$line, $cu_done/*.'/'.$cu_count*/)//Do not display the catchup possible count, because it is limited to 3 (and possible count are often 6)
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
						
						// completion
						$this->XlActiveSheet
							->setCellValue('U'.$line, $comments) //Comments
							->setCellValueExplicit('V'.$line, ($total_time/86400), \PHPExcel_Cell_DataType::TYPE_NUMERIC) //Total time
							->setCellValue('W'.$line, $this->toExcelDateFormat($lastAccess))
						;
					}
				}
			}
			$PM = isset($this->_view->pm)?$this->_view->pm:'YES';
			$this->setExcelFinalFormat($line);
			$this->sendXlsx($branchname);
		}
	}
	
	function buildDataTree($dbdata){
		if (!empty($dbdata)){
			foreach ($dbdata as $i=>$row){
				if (empty($row['user_id'])) continue;
				$uid = $row['user_id'];
				if (!isset($this->_view->data[$uid])){
					foreach (array('login', 'firstname', 'lastname', 'email', 'recommended_level', 'acquired_level', 'country', 'branch_name', 'branch_id', 'parent_branch_name') as $field)
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
	}
	
	
	function retrieveGeneric($code){
		// TODO : Generic retrieve of DB entries
		$branches = 'SELECT * FROM BranchInfo;';
		$BITable = $this->getDb($this->dbinstancename)->getTable($branches);
		foreach ($BITable as $key => $values){
			if($values['branch_id'] == $code){
				$this->_view->pm = strtoupper($values['branch_name']);
				break;
			}
		}
		$this->getTreeFromPM($code, $BITable);
		$query =
			'SELECT DISTINCT '.
			'VG.* '.
			'FROM V_GENERIC AS VG '.
			'WHERE VG.branch_id IN ('.implode(',', $this->tree).') ';
			//'ORDER BY VG.lastname ASC , VG.firstname ASC , VG.course_id ASC';
		if(!empty($_REQUEST['debug'])) $query .= ' LIMIT 100';
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
		foreach (array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W') AS $aCol){
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