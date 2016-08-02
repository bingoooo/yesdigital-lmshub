<?php
namespace FragTale\Controller\Yny_Reports\Export\Xlsx;
use FragTale\Controller\Yny_Reports\Export\Xlsx;

/**
 * @author fabrice
 */
class Generic extends Xlsx{
	
	function main(){
		//Retrieving and sorting data
		$this->buildDataTree($this->retrieveData('kn'));
		
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
						->setCellValue('A'.$line, $User['email'])
						->setCellValue('B'.$line, strtoupper($User['country']))//Country
						->setCellValue('C'.$line, strtoupper($User['firstname']))
						->setCellValue('D'.$line, !empty($User['lastname']) ? strtoupper($User['lastname']) : trim($User['login'], '/'))
						->setCellValue('E'.$line, $User['recommended_level'])//Starting level
						->setCellValue('F'.$line, $User['acquired_level'])//Current level
						->setCellValue('G'.$line, $LP['path_name'])//Booked program
						->setCellValue('H'.$line, $LP['user_lp_date_begin_validity'])
						->setCellValue('I'.$line, $LP['user_lp_date_end_validity'])
						->setCellValue('J'.$line, strtoupper($User['branch_name']))//Branch
						->setCellValue('K'.$line, $lpstartdate)
						->setCellValue('L'.$line, $lpenddate)
						;
					
					$elearnings = $microlearnings = $sessions = array();
					$globalTime = $iCol = $nbElearnings = $nbElCompleted = $nbSessionPassed = 0;
					
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
					//$completion
					$lastAccess = (stripos($lastAccess, '0000-00-00')!==false || empty($lastAccess)) ? null : \PHPExcel_Shared_Date::PHPToExcel(strtotime($lastAccess)); 
					$this->XlActiveSheet
						->setCellValue('L'.$line, $nbElearnings)//Amount of modules in learning plan
						->setCellValue('AE'.$line, $globalTime)//Total time en heures
						->setCellValue('AF'.$line, $lastAccess)
					;
				}
			}
			$this->setExcelFinalFormat($line);
			$this->sendXlsx('Generic');
		}
	}
	
	function setExcelFinalFormat($finalrowindex){
		//Set center alignment for columns
		foreach (array('G', 'H', 'J', 'K', 'L', 'M', 'N', 'P', 'R', 'T', 'V', 'X', 'Z', 'AB', 'AC', 'AD', 'AE', 'AF') AS $aCol){
			$this->XlActiveSheet->getStyle($aCol.'2:'.$aCol.$finalrowindex)
			->getAlignment()
			->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		}
		//Set date format
		foreach (array('J', 'K', 'P', 'R', 'T', 'V', 'X', 'Z', 'AF') as $aCol){
			$this->XlActiveSheet->getStyle($aCol.'2:'.$aCol.$finalrowindex)
			->getNumberFormat()
			->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_DATE_DDMMYYYY);
		}
	}
	
}