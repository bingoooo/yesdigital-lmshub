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
			$line = 2;
			foreach ($this->_view->data as $uid=>$User){
				if (empty($User['learning_plans'])) continue;
				foreach ($User['learning_plans'] as $path_id=>$LP){
					$lpstartdate= (stripos($LP['user_lp_date_begin_validity'], '0000-00-00')!==false || empty($LP['user_lp_date_begin_validity'])) ? '' : date('d/m/Y', strtotime($LP['user_lp_date_begin_validity'])); 
					$lpenddate	= (stripos($LP['user_lp_date_end_validity'], '0000-00-00')!==false || empty($LP['user_lp_date_end_validity'])) ? '' : date('d/m/Y', strtotime($LP['user_lp_date_end_validity'])); 
					$this->PHPXL->setActiveSheetIndex(0)
						->setCellValue('A'.$line, $User['firstname'])
						->setCellValue('B'.$line, !empty($User['lastname']) ? $User['lastname'] : trim($User['login'], '/'))
						->setCellValue('C'.$line, $User['email'])
						->setCellValue('D'.$line, '')//Country
						->setCellValue('E'.$line, '')//Branch
						->setCellValue('F'.$line, '')//BU/FU
						->setCellValue('G'.$line, $User['recommended_level'])//Starting level
						->setCellValue('H'.$line, $User['acquired_level'])//Current level
						->setCellValue('I'.$line, $LP['path_name'])//Booked program
						->setCellValue('J'.$line, $lpstartdate)
						->setCellValue('K'.$line, $lpenddate)
						->setCellValue('N'.$line, '')//On track ??
					;
					
					$elearnings = $microlearnings = $sessions = $lsat = array();
					$globalTime = $iCol = $nbElearnings = $nbElCompleted = $nbSessionPassed = 0;
					
					$lastAccess = null;
					$courseColumns = array('O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
					//Get courses
					if (stripos($LP['path_name'], 'lsat')!==false){
						if (!empty($LP['courses'])){
							foreach ($LP['courses'] as $course_id=>$Course){
								$globalTime += (int)$Course['user_course_timespent'];
								if (!empty($Course['user_course_date_last_access'])){
									if (!$lastAccess || $lastAccess < $Course['user_course_date_last_access'])
										$lastAccess = $Course['user_course_date_last_access'];
								}
								$Course['module_status'] = (!empty($Course['user_course_date_completed']) && stripos($Course['user_course_date_completed'], '0000-00-00')===false) ? 'Completed' :
									(empty($Course['user_course_date_first_access']) || stripos($Course['user_course_date_first_access'], '0000-00-00')!==false ? 'Not started' : 'In progress');
								if ($Course['module_status']==='Completed')
									$nbElCompleted++;
								$lsat[$course_id] = $Course;
							}
						}
					}
					else{
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
					}
					if (!empty($lsat)){
						$nbElearnings = count($lsat);
						foreach ($lsat as $course_id=>$Course){
							$completiondate = (stripos($Course['user_course_date_completed'], '0000-00-00')!==false || empty($Course['user_course_date_completed'])) ? '' : date('d/m/Y', strtotime($Course['user_course_date_completed'])); 
							$this->PHPXL->setActiveSheetIndex(0)
								->setCellValue($courseColumns[$iCol].$line, $Course['module_status'])
								->setCellValue($courseColumns[$iCol+1].$line, $completiondate)
							;
							$iCol+=2;
						}
					}
					else{
						if (!empty($elearnings)){
							$nbElearnings = count($elearnings);
							foreach ($elearnings as $course_id=>$Course){
								$completiondate = (stripos($Course['user_course_date_completed'], '0000-00-00')!==false || empty($Course['user_course_date_completed'])) ? '' : date('d/m/Y', strtotime($Course['user_course_date_completed'])); 
								$this->PHPXL->setActiveSheetIndex(0)
									->setCellValue($courseColumns[$iCol].$line, $Course['module_status'])
									->setCellValue($courseColumns[$iCol+1].$line, $completiondate)
								;
								$iCol+=2;
							}
						}
						if (!empty($sessions)){
							$this->PHPXL->setActiveSheetIndex(0)
								->setCellValue('AC'.$line, count($sessions))
								->setCellValue('AD'.$line, $nbSessionPassed)
							;
						}
						if (!empty($microlearnings)){
							$Course = reset($microlearnings);
							$mlStatus = (!empty($Course['user_course_date_completed']) && stripos($Course['user_course_date_completed'], '0000-00-00')===false) ? 'Completed' :
											(empty($Course['user_course_date_first_access']) || stripos($Course['user_course_date_first_access'], '0000-00-00')!==false ? 'Not started' : 'In progress');
							$this->PHPXL->setActiveSheetIndex(0)
								->setCellValue('AA'.$line, $mlStatus)
								->setCellValue('AM'.$line, '')//Completion of ML
							;
						}
					}
					//$completion
					$lastAccess = (stripos($lastAccess, '0000-00-00')!==false || empty($lastAccess)) ? '' : date('d/m/Y', strtotime($lastAccess)); 
					$this->PHPXL->setActiveSheetIndex(0)
						->setCellValue('L'.$line, $nbElearnings)//Amount of modules in learning plan
						->setCellValue('AE'.$line, $globalTime)//Total time en heures
						->setCellValue('AF'.$line, $lastAccess)
					;
					$line++;
				}
			}
			$this->sendXlsx('BNP');
		}
	}
}