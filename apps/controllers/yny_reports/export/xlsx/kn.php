<?php
namespace FragTale\Controller\Yny_Reports\Export\Xlsx;
use FragTale\Controller\Yny_Reports\Export\Xlsx;

/**
 * @author fabrice
 */
class Kn extends Xlsx{
	
	function main(){
		//Retrieving and sorting data
		$this->buildDataTree($this->retrieveData('kn'));
		
		//Building Excel file
		if (!empty($this->_view->data) && empty($_REQUEST['debug'])){
			$this->PHPXL = \PHPExcel_IOFactory::load(TPL_ROOT.'/xlsx/kn.xlsx');
			$this->XlActiveSheet = $this->PHPXL->setActiveSheetIndex(0);
			$line = 1;
			foreach ($this->_view->data as $uid=>$User){
				if (empty($User['learning_plans'])) continue;
				foreach ($User['learning_plans'] as $path_id=>$LP){
					if ($path_id === 'UNKNOWN') continue;
					$line++;
					$startDate = (!empty($LP['user_lp_date_begin_validity']) || stripos($LP['user_lp_date_begin_validity'], '0000-00-00')!==false) ? $LP['user_lp_date_begin_validity'] : $LP['user_lp_date_assign'];
					$this->XlActiveSheet
						->setCellValue('A'.$line, strtoupper($User['firstname']))
						->setCellValue('B'.$line, !empty($User['lastname']) ? strtoupper($User['lastname']) : trim($User['login'], '/'))
						->setCellValue('C'.$line, $User['email'])
						->setCellValue('D'.$line, strtoupper($User['country']))//Country
						->setCellValue('E'.$line, strtoupper($User['branch_name']))//Branch
						->setCellValue('F'.$line, '')//BU/FU
						->setCellValue('G'.$line, $User['recommended_level'])//Starting level
						->setCellValue('H'.$line, $User['acquired_level'])//Current level
						->setCellValue('I'.$line, $LP['path_name'])//Booked program
						->setCellValue('J'.$line, $this->toExcelDateFormat($startDate))
						->setCellValue('K'.$line, $this->toExcelDateFormat($LP['user_lp_date_end_validity']))
						->setCellValue('N'.$line, '')//On track ??
					;
					
					$elearnings = $microlearnings = $sessions = $esps = $catchups = array();
					$globalTime = $iCol = $nbElearnings = $nbElCompleted = $nbSessionPassed = $nbCatchupsTaken = 0;
					
					$lastAccess = null;
					$courseColumns = array('O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
					//Get courses
					if (!empty($LP['courses'])){
						foreach ($LP['courses'] as $course_id=>$Course){
							$globalTime += (int)$Course['user_course_timespent'];
							if (!empty($Course['user_course_date_last_access'])){
								if (!$lastAccess || $lastAccess < $Course['user_course_date_last_access'])
									$lastAccess = $Course['user_course_date_last_access'];
							}
							if ($Course['course_type']==='elearning'){
								if (stripos($Course['course_name'], 'micro')!==false)
									$microlearnings[$course_id] = $Course;
								else{
									$Course['module_status'] = (!empty($Course['user_course_date_completed']) && stripos($Course['user_course_date_completed'], '0000-00-00')===false) ? 'Completed' :
										(empty($Course['user_course_date_first_access']) || stripos($Course['user_course_date_first_access'], '0000-00-00')!==false ? 'Not started' : 'In progress');
									if ($Course['module_status']==='Completed')
										$nbElCompleted++;
									$elearnings[$course_id] = $Course;
								}
							}
							else{
								if ((!empty($Course['user_course_date_completed']) && stripos($Course['user_course_date_completed'], '0000-00-00')===false) || $Course['user_course_status']==2)
									$nbSessionPassed++;
								$sessions[$course_id] = $Course;
							}
						}
					}
					// handle catchups & ESPs as they have no learning plans.
					// + They need to be displayed only once in the first LP found
					if (!empty($User['learning_plans']['UNKNOWN']['courses'])){
						foreach ($User['learning_plans']['UNKNOWN']['courses'] as $course_id=>$Course){
							if (!empty($Course['user_course_date_last_access'])){
								if (!$lastAccess || $lastAccess < $Course['user_course_date_last_access'])
									$lastAccess = $Course['user_course_date_last_access'];
							}
							if(stripos($Course['course_code'], 'ESP')!==false){
								if (empty($User['learning_plans']['UNKNOWN']['esp_counted_yet']))
									$esps[$course_id] = $Course;
								continue;
							}
							$catchpos = stripos($Course['course_code'], 'CATCH');
							if (!empty($sessions) && $catchpos!==false){
								// We have to match if this catchup match a corresponding SKS in this Learning plan
								$exp2match = trim(substr($Course['course_code'], 0, $catchpos), '_');
								foreach ($sessions as $session){
									if (stripos($session['course_code'], $exp2match)!==false){
										$catchups[$course_id] = $Course;
									}
								}
							}
						}
						$User['learning_plans']['UNKNOWN']['esp_counted_yet'] = true;
					}
					
					if (!empty($elearnings)){
						$nbElearnings = count($elearnings);
						foreach ($elearnings as $course_id=>$Course){
							$completiondate = (stripos($Course['user_course_date_completed'], '0000-00-00')!==false || empty($Course['user_course_date_completed'])) ? null : \PHPExcel_Shared_Date::PHPToExcel(strtotime($Course['user_course_date_completed'])); 
							$this->XlActiveSheet
								->setCellValue($courseColumns[$iCol].$line, $Course['module_status'])
								->setCellValue($courseColumns[$iCol+1].$line, $completiondate)
							;
							$iCol+=2;
						}
					}
					$this->XlActiveSheet->setCellValue('L'.$line, $nbElearnings);//Amount of modules in learning plan
					
					if (!empty($sessions)){
						$this->XlActiveSheet
							->setCellValue('AC'.$line, count($sessions))
							->setCellValue('AD'.$line, $nbSessionPassed)
						;
					}
					if (!empty($microlearnings)){
						$Course = reset($microlearnings);
						$mlStatus = (!empty($Course['user_course_date_completed']) && stripos($Course['user_course_date_completed'], '0000-00-00')===false) ? 'Completed' :
										(empty($Course['user_course_date_first_access']) || stripos($Course['user_course_date_first_access'], '0000-00-00')!==false ? 'Not started' : 'In progress');
						$this->XlActiveSheet
							->setCellValue('AA'.$line, $mlStatus)
							->setCellValue('AM'.$line, '')//Completion of ML
						;
					}
					if (!empty($esps)){
						$esp_time = $esp_done = 0;
						foreach ($esps as $course_id => $Course){
							$esp_time += $Course['user_course_timespent'];
							if($Course['user_course_status'] == 2) $esp_done++;
						}
						$this->XlActiveSheet->setCellValue('AF'.$line, $esp_done.'/'.count($esps));
						$globalTime += $esp_time;
					}
					if (!empty($catchups)){
						$cu_time = 0;
						$cu_count= count($catchups);
						foreach ($catchups as $course_id => $Course){
							$cu_time += $Course['user_course_timespent'];
							if($Course['user_course_status'] == 2) $nbCatchupsTaken++;
						}
						$this->XlActiveSheet->setCellValue('AE'.$line, $nbCatchupsTaken);
						$globalTime += $cu_time;
					}
					//$completion
					$this->XlActiveSheet
						->setCellValueExplicit('AG'.$line, ($globalTime/86400), \PHPExcel_Cell_DataType::TYPE_NUMERIC) //Total time en heures
						->setCellValue('AH'.$line, $this->toExcelDateFormat($lastAccess));
				}
			}
			$this->setExcelFinalFormat($line);
			$this->sendXlsx('K&N');
		}
	}
	
	function setExcelFinalFormat($finalrowindex){
		//Set center alignment for columns
		foreach (array('G', 'H', 'J', 'K', 'L', 'M', 'N', 'P', 'R', 'T', 'V', 'X', 'Z', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH') AS $aCol){
			$this->XlActiveSheet->getStyle($aCol.'2:'.$aCol.$finalrowindex)
			->getAlignment()
			->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		}
		//Set date format
		foreach (array('J', 'K', 'P', 'R', 'T', 'V', 'X', 'Z', 'AH') as $aCol){
			$this->XlActiveSheet->getStyle($aCol.'2:'.$aCol.$finalrowindex)
			->getNumberFormat()
			->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_DATE_DDMMYYYY);
		}
		//Set time formats
		foreach (array('AG') as $aCol){
			$this->XlActiveSheet
				->getStyle($aCol.'2:'.$aCol.$finalrowindex)
				->getNumberFormat()->setFormatCode('[HH]:MM')
			;
		}
	}
	
}