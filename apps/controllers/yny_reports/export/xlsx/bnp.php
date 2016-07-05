<?php
namespace FragTale\Controller\Yny_Reports\Export\Xlsx;
use FragTale\Controller\Yny_Reports\Export\Xlsx;

/**
 * @author fabrice
 */
class Bnp extends Xlsx{
	
	function main(){
		//Retrieving and sorting data
		$this->buildDataTree($this->retrieveData('bnp'));
		
		//Building Excel file
		if (!empty($this->_view->data) && empty($_REQUEST['debug'])){
			$this->PHPXL = \PHPExcel_IOFactory::load(TPL_ROOT.'/xlsx/bnp.xlsx');
			$line = 3;
			foreach ($this->_view->data as $uid=>$User){
				if (empty($User['learning_plans'])) continue;
				foreach ($User['learning_plans'] as $path_id=>$LP){
					$this->PHPXL->setActiveSheetIndex(0)
						->setCellValue('A'.$line, $User['lastname'])
						->setCellValue('B'.$line, !empty($User['firstname']) ? $User['firstname'] : trim($User['login'], '/'))
						->setCellValue('C'.$line, $uid)
						->setCellValue('D'.$line, $LP['path_name'])
						->setCellValue('E'.$line, $LP['user_lp_date_begin_validity'])//Date de début de parcours
						->setCellValue('F'.$line, $LP['user_lp_date_end_validity'])//Date de fin de parcours
					;
					
					$elearnings = $microlearnings = $sessions = $lsat = array();
					$globalTime = 0;
					//Get courses
					if (stripos($LP['path_name'], 'lsat')!==false){
						if (!empty($LP['courses'])){
							$lsat['module_count'] = count($LP['courses']);
							$lsat['module_done'] = 0;
							foreach ($LP['courses'] as $course_id=>$Course){
								$globalTime += (int)$Course['user_course_timespent'];
								$lsat[$course_id] = $Course;
								if ($Course['user_course_status'])
									$lsat['module_done']++;
							}
						}
					}
					else{
						if (!empty($LP['courses'])){
							foreach ($LP['courses'] as $course_id=>$Course){
								$globalTime += (int)$Course['user_course_timespent'];
								if ($Course['course_type']==='elearning'){
									if (stripos($Course['course_name'], 'micro')!==false)
										$microlearnings[$course_id] = $Course;
									else
										$elearnings[$course_id] = $Course;
								}
								else
									$sessions[$course_id] = $Course;
							}
						}
					}
					if (!empty($lsat))
						$this->PHPXL->setActiveSheetIndex(0)
							->setCellValue('G'.$line, '')//Objectif en heures
							->setCellValue('H'.$line, $lsat['module_done'].'/'.$lsat['module_count'])
							->setCellValue('I'.$line, $globalTime)
							->setCellValue('J'.$line, $lsat['module_done']!=$lsat['module_count']?'Incomplete':'Complete')
						;
					if (!empty($microlearnings)){
						$microlearning = reset($microlearnings);
						$this->PHPXL->setActiveSheetIndex(0)
							->setCellValue('N'.$line, '')//Objectif en heures
							//->setCellValue('O'.$line, '')//ML rélaisés
							->setCellValue('P'.$line, $microlearning['user_course_timespent'])
							//->setCellValue('Q'.$line, '')//Avancée du microlearning
						;
					}
					/*if (!empty($elearnings))
						$this->PHPXL->setActiveSheetIndex(0)
							->setCellValue('G'.$line, '')//Objectif en heures
							->setCellValue('H'.$line, $User['lastname'])
							->setCellValue('I'.$line, $User['firstname'])
							->setCellValue('J'.$line, $uid)
						;*/
					if (!empty($sessions)){
						$nbSessions = count($sessions);
						$nbDone = 0;
						foreach ($sessions as $session){
							if ($session['user_course_status'])
								$nbDone++;
						}
						$this->PHPXL->setActiveSheetIndex(0)
							->setCellValue('K'.$line, '')//Objectif en heures
							->setCellValue('L'.$line, $nbDone.'/'.$nbSessions)
							->setCellValue('M'.$line, $nbDone!=$nbSessions?'Incomplete':'Complete')
						;
					}
					
					$this->PHPXL->setActiveSheetIndex(0)
						->setCellValue('R'.$line, $globalTime)//Total time en heures
					;
					$line++;
				}
			}
			$this->sendXlsx('BNP');
		}
	}
}