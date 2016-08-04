<?php
namespace FragTale\Controller\Yny_Reports\Export\Xlsx;
use FragTale\Controller\Yny_Reports\Export\Xlsx;

/**
 * @author fabrice
 */
class Generic extends Xlsx{
	
	function main(){
		//Retrieving and sorting data
		$data = 'kn';
		if(isset($_REQUEST['data'])) $data = $_REQUEST['data'];
		$this->buildDataTree($this->retrieveData($data));
		
		//Building Excel file
		if (!empty($this->_view->data) && empty($_REQUEST['debug'])){
			$this->PHPXL = \PHPExcel_IOFactory::load(TPL_ROOT.'/xlsx/generic.xlsx');
			$this->XlActiveSheet = $this->PHPXL->setActiveSheetIndex(0);
			$line = 2;
			foreach ($this->_view->data as $uid=>$User){
				if (empty($User['learning_plans'])) continue;
				foreach ($User['learning_plans'] as $path_id=>$LP){
					$line++;
					$lpstartdate= (stripos($LP['user_lp_date_begin_validity'], '0000-00-00')!==false || empty($LP['user_lp_date_begin_validity'])) ? null : \PHPExcel_Shared_Date::PHPToExcel(strtotime($LP['user_lp_date_begin_validity'])); 
					$lpenddate	= (stripos($LP['user_lp_date_end_validity'], '0000-00-00')!==false || empty($LP['user_lp_date_end_validity'])) ? null : \PHPExcel_Shared_Date::PHPToExcel(strtotime($LP['user_lp_date_end_validity'])); 
					$this->XlActiveSheet
						->setCellValue('A'.$line, 'TODO' /*$LP['path_name']*/)//Account
						->setCellValue('B'.$line, 'TODO' /*strtoupper($User['contract'])*/)//Contract
						->setCellValue('C'.$line, strtoupper($User['firstname']))
						->setCellValue('D'.$line, !empty($User['lastname']) ? strtoupper($User['lastname']) : trim($User['login'], '/'))
						->setCellValue('E'.$line, $User['recommended_level'])//Starting level
						->setCellValue('F'.$line, $User['acquired_level'])//Current level
						->setCellValue('G'.$line, $LP['path_name'])//Booked program
						->setCellValue('H'.$line, $lpstartdate)
						->setCellValue('I'.$line, $lpenddate)
						// ->setCellValue('J'.$line, strtoupper($User['branch_name']))//Branch
						// ->setCellValue('K'.$line, $lpstartdate)
						// ->setCellValue('L'.$line, $lpenddate)
						;
					
					$elearnings = $microlearnings = $sessions = $catch_up = $esp = $business_keys = array();
					$globalTime = $iCol = $nbElearnings = $nbElCompleted = $nbSessionPassed = 0;
					$comments = 'TODO'; // Add a commentary column
					
					$lastAccess = null;
					$courseColumns = array('O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
					//Get courses					
					if (!empty($LP['courses'])){
						foreach ($LP['courses'] as $course_id=>$Course){
							if($Course['user_course_date_last_access'] !== '0000-00-00 00:00:00'){
								$lastAccess = $Course['user_course_date_last_access'];
							}
								
							if(strpos($Course['course_code'], 'ESP')!==false){
								$esp[$course_id] = $Course;
							}
																					
							if(strpos($Course['course_code'], 'SKS')!==false){
								$sessions[$course_id] = $Course;
							}
							if (strpos($Course['course_code'], 'CATCH')!==false){
								$catch_up[$course_id] = $Course;
							}
							if (strpos($Course['course_code'], 'BK')!==false || stripos($Course['course_label'], 'business keys')!==false){
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
					
					if (!empty($elearnings)){
						$el_done = 0;
						$el_count = count($elearnings);
						$el_time = null;
						foreach ($elearnings as $course_id => $Course){
							$el_time += $Course['user_course_timespent'];
							if($Course['user_course_score'] !== '0.00')
								$el_done += 1;
						}
						$this->XlActiveSheet
							->setCellValue('J'.$line, $el_done.'/'.$el_count)
							->setCellValue('K'.$line, $el_time)
						;
					}
					if (!empty($sessions)){
						$sessions_done = 0;
						$sessions_count = count($sessions);
						$sessions_time = null;
						foreach ($sessions as $course_id => $Course){
							$sessions_time += $Course['user_course_timespent'];
							if($Course['user_course_score'] !== '0.00') $sessions_done += 1;
						}
						$this->XlActiveSheet
							->setCellValue('L'.$line, $sessions_done.'/'.$sessions_count)
							->setCellValue('M'.$line, $sessions_time)
						;
					}
					
					if (!empty($catch_up)){
						$cu_done = 0;
						$cu_count = count($catch_up);
						$cu_time = null;
						foreach ($catch_up as $course_id => $Course){
							$cu_time += $Course['user_course_timespent'];
							if($Course['user_course_score'] !== '0.00') $cu_done += 1;
						}
						$this->XlActiveSheet
							->setCellValue('N'.$line, $cu_done.'/'.$cu_count)
							->setCellValue('O'.$line, $cu_time)
						;
					}
					
					if (!empty($microlearnings)){
						$ml_time = null;
						foreach ($microlearnings as $course_id => $Course){
							$ml_time += $Course['user_course_timespent'];
						}
						$this->XlActiveSheet
							->setCellValue('P'.$line, $ml_time)
						;
					}
					
					if(!empty($esp)){
						$esp_done = 0;
						$esp_count = count($esp);
						$esp_time = null;
						foreach ($esp as $course_id => $Course){
							$esp_time += $Course['user_course_timespent'];
							if($Course['user_course_score'] !== '0.00') $esp_done += 1;
						}
						$this->XlActiveSheet
							->setCellValue('Q'.$line, $esp_done.'/'.$esp_count)
							->setCellValue('R'.$line, $esp_time)
						;
					}
					
					if(!empty($business_keys)){
						$business_keys_done = 0;
						$business_keys_count = count($business_keys);
						$business_keys_time = null;
						foreach ($business_keys as $course_id => $Course){
							$business_keys_time += $Course['user_course_timespent'];
							if($Course['user_course_score'] !== '0.00') $business_keys_done += 1;
						}
						$this->XlActiveSheet
							->setCellValue('S'.$line, $business_keys_done.'/'.$business_keys_count)
							->setCellValue('T'.$line, $business_keys_time)
						;
					}
					
					//$completion
					$lastAccess = (stripos($lastAccess, '0000-00-00')!==false || empty($lastAccess)) ? null : \PHPExcel_Shared_Date::PHPToExcel(strtotime($lastAccess)); 
					$this->XlActiveSheet
						->setCellValue('U'.$line, $comments)//Comments
						->setCellValue('V'.$line, $lastAccess)
					;
				}
			}
			$this->setExcelFinalFormat($line);
			$this->sendXlsx('Generic');
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
		foreach (array('H', 'I', 'R', 'T', 'V', 'X', 'Z', 'AF') as $aCol){
			$this->XlActiveSheet->getStyle($aCol.'2:'.$aCol.$finalrowindex)
			->getNumberFormat()
			->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_DATE_DDMMYYYY);
		}
	}
	
}