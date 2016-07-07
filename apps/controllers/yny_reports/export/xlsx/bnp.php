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
			$finalData = array();
			//Final Sorting: by path type
			foreach ($this->_view->data as $uid=>$User){
				if (!empty($User['courses'])){
					$globalTime = 0;
					$Courses = array();
					$pathType= $this->definePathType($User['courses']);
					foreach ($User['courses'] as $course_id=>$Course){
						$globalTime += (int)$Course['user_course_timespent'];
						if ($Course['course_type']==='elearning'){
							if (stripos($Course['course_name'], 'microlearning')!==false)
								$Courses['ML'][$course_id] = $Course;
							else
								$Courses['EL'][$course_id] = $Course;
						}
						elseif (stripos($Course['course_code'], 'BK_')!==false)
							$Courses['BK'][$course_id] = $Course;//Business keys, goes to "Atelier..."
						elseif (stripos($Course['course_code'], 'ESP_')!==false)
							$Courses['ESP'][$course_id] = $Course;//Webcoaching
						else
							$Courses['SKS'][$course_id] = $Course;
					}
					$User['total_time_spent']	= $globalTime;
					$User['courses']			= $Courses;
					$finalData[$pathType][$uid]	= $User;
				}
			}
			$this->PHPXL = \PHPExcel_IOFactory::load(TPL_ROOT.'/xlsx/bnp.xlsx');
			$iUser	= 0;
			$line	= 3;
			foreach ($finalData as $pathTypeName=>$PathType){
				foreach ($PathType as $uid=>$User){
					$line++;
					$iUser++;
					
					if (!empty($User['user_lp_date_begin_validity']) && strpos($User['user_lp_date_begin_validity'], '0000')===false){
						$t_year   = substr($User['user_lp_date_begin_validity'],0,4);
						$t_month  = substr($User['user_lp_date_begin_validity'],5,2);// Fixed problems with offsets
						$t_day    = substr($User['user_lp_date_begin_validity'],7,2);
						$t_date   = \PHPExcel_Shared_Date::FormattedPHPToExcel($t_year, $t_month, $t_day);
					}
					else $t_date = null;
					$this->PHPXL->setActiveSheetIndex(0)
						->setCellValue('A'.$line, $iUser)
						->setCellValue('B'.$line, !empty($User['firstname']) ? strtoupper($User['lastname']) : trim($User['login'], '/'))
						->setCellValue('C'.$line, strtoupper($User['firstname']))
						->setCellValue('D'.$line, ''/*$uid*/)
						->setCellValue('E'.$line, $pathTypeName)
						->setCellValue('F'.$line, $User['recommended_level'])//Date de début de parcours
						->setCellValue('G'.$line, $t_date)//Date de début de parcours
					;
					
					#PRE-LEARNING
					if (!empty($User['courses']['EL'])){
						$nbDone = $elTimespent = 0;
						foreach ($User['courses']['EL'] as $EL){
							//Calculate timespent on elearnings
							$elTimespent += (float)$EL['user_course_timespent'];
							//Check EL done checking the dates
							if (!empty($EL['user_course_date_first_access']) && stripos($EL['user_course_date_first_access'], '0000-00-00')===false){
								//Here, the user has at least begun its elearning
								if (!empty($EL['user_course_date_completed']) && stripos($EL['user_course_date_completed'], '0000-00-00')===false){
									//It is completed
									$nbDone++;
								}
								else $nbDone+= .5;
							}
							//$nbDone += (float)$EL['user_course_status'];
						}
						//$strTimespent = "Estimé : ".(($nbDone*1.5)*15/360)."\nRéalisé : ".$elTimespent;
						$this->PHPXL->setActiveSheetIndex(0)
							->setCellValue('I'.$line, "=TIME(9;0;0)")//Objectif
							->setCellValue('J'.$line, $nbDone)//Modules réalisés
							//->setCellValue('K'.$line, $strTimespent)//Temps en heures
						;
					}
					else $this->PHPXL->setActiveSheetIndex(0)->setCellValue('I'.$line, null);
					
					#Cours formateur
					if (!empty($User['courses']['SKS'])){
						$nbSessions = count($User['courses']['SKS']);
						$nbDone = 0;
						foreach ($User['courses']['SKS'] as $session){
							if (!empty($session['user_course_date_completed']) && stripos($session['user_course_date_completed'], '0000-00-00')===false){
								$nbDone += (strtolower($session['course_type'])==='telephone') ? .5 : 1;
							}
						}
						//$strDone = '0'.(int)$nbDone.':'.(is_int($nbDone)? '00' : '30').':00';
						$this->PHPXL->setActiveSheetIndex(0)
							->setCellValue('M'.$line, '=6/24')//Objectifs
							->setCellValue('N'.$line, "=$nbDone/24")
						;
					}
					else $this->PHPXL->setActiveSheetIndex(0)->setCellValue('M'.$line, null);
					
					#Microlearning
					if (!empty($User['courses']['ML'])){
						$microlearning = reset($User['courses']['ML']);
						$this->PHPXL->setActiveSheetIndex(0)
							->setCellValue('P'.$line, '=5/24')//Objectifs
					 		//->setCellValue('Q'.$line, '')//ML réalisés
					 		//->setCellValue('R'.$line, $microlearning['user_course_timespent'])
					 	;
					 }
					 else $this->PHPXL->setActiveSheetIndex(0)->setCellValue('P'.$line, null);
					
					 #Webcoaching
					 if (!empty($User['courses']['ESP'])){
						$nbDone = 0;
						foreach ($User['courses']['ESP'] as $session){
							if (!empty($session['user_course_date_completed']) && stripos($session['user_course_date_completed'], '0000-00-00')===false){
								//It is completed
								if (strtolower($session['course_type'])==='telephone')
									$nbDone += .5;
								else
									$nbDone += 1;
							}
						}
						$strTime = '0'.(int)$nbDone.':'.(is_int($nbDone)? '00' : '30').':00';
					 	$this->PHPXL->setActiveSheetIndex(0)
					 		->setCellValue('T'.$line, '=8/24')//Objectifs
					 		->setCellValue('U'.$line, $nbDone)
					 		->setCellValue('V'.$line, $strTime)
					 	;
					 }
					 else $this->PHPXL->setActiveSheetIndex(0)->setCellValue('P'.$line, null);
					 
					 #Ateliers
					 if (!empty($User['courses']['BK'])){
						$nbDone = $timeSpent = 0;
						foreach ($User['courses']['BK'] as $session){
							if (!empty($session['user_course_date_completed']) && stripos($session['user_course_date_completed'], '0000-00-00')===false){
								//It is completed
								if (strtolower($session['course_type'])==='telephone')
									$timeSpent += .5;
								else
									$timeSpent += 1;
								$nbDone++;
							}
						}
						//$strTime = '0'.(int)$timeSpent.':'.(is_int($timeSpent)? '00' : '30').':00';
					 	$this->PHPXL->setActiveSheetIndex(0)
							->setCellValue('W'.$line, '=(12/24)')//Objectifs
					 		->setCellValue('X'.$line, $nbDone)
					 		->setCellValue('Y'.$line, '='.$timeSpent.'/24')
					 	;
					 }
					 else $this->PHPXL->setActiveSheetIndex(0)->setCellValue('W'.$line, null);
				
					## Formules Excel
					$this->setExcelFormulas($line);
					## Formats
					$this->formatExcelRow($line);
				}
			}
			$this->sendXlsx('BNP');
		}
	}
	
	function buildDataTree($dbdata){
		if (!empty($dbdata)){
			foreach ($dbdata as $i=>$row){
				if (empty($row['user_id'])) continue;
				$uid = $row['user_id'];
				if (!isset($this->_view->data[$uid])){
					foreach (array('login', 'firstname', 'lastname', 'email', 'recommended_level', 'acquired_level', 'user_lp_date_begin_validity') as $field)
						$this->_view->data[$uid][$field] = isset($row[$field]) ? $row[$field] : null;
				}
				if (!empty($row['user_lp_date_begin_validity'])){
					if (empty($this->_view->data[$uid]['user_lp_date_begin_validity']) || $row['user_lp_date_begin_validity']<$this->_view->data[$uid]['user_lp_date_begin_validity']){
						$this->_view->data[$uid]['user_lp_date_begin_validity'] = $row['user_lp_date_begin_validity'];
					}
				}
				if (!empty($row['course_id'])){
					$course_id = (int)$row['course_id'];
					if (!isset($this->_view->data[$uid]['courses'][$course_id])){
						foreach (array(
								'course_code',
								'course_name',
								'course_txt',
								//'course_image',
								//'course_language',
								//'course_status',
								'course_type',
								'course_sub_start_date',
								'course_sub_end_date',
								'course_date_begin',
								'course_date_end',
								//'course_link',
								'course_category_name',
								'course_label',
								//'course_certificate_enable',
								//'course_certificate_name',
								'user_course_date_inscripted',
								'user_course_date_first_access',
								'user_course_date_last_access',
								'user_course_date_completed',
								'user_course_status',
								'user_course_waiting',
								'user_course_score',
								'user_course_timespent',
								'path_code',
								'path_name',
								'path_txt',
								//'create_date',
								//'img_url',
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
							$this->_view->data[$uid]['courses'][$course_id][$field] = isset($row[$field]) ? $row[$field] : null;
					}
				}
			}
		}
	}
	
	function formatExcelRow($line){
		//Set borders
		foreach (array('A', 'D', 'H', 'L', 'O', 'S', 'V', 'Z', 'AB') as $alphacol){
			$this->PHPXL->setActiveSheetIndex(0)
				->getStyle($alphacol.$line)->getBorders()->getRight()->setBorderStyle(\PHPExcel_Style_Border::BORDER_MEDIUM)
				->setColor(new \PHPExcel_Style_Color(\PHPExcel_Style_Color::COLOR_BLACK))
			;
		}
		//Set date format
		$this->PHPXL->setActiveSheetIndex(0)
			->getStyle('G'.$line)
			->getNumberFormat()->setFormatCode('DD/MM/YYYY')
		;
		//Set time formats
		foreach (array('H') as $alphacol){
			$this->PHPXL->setActiveSheetIndex(0)
				->getStyle($alphacol.$line)
				->getNumberFormat()->setFormatCode('[H]:MM:SS')
			;
		}
		foreach (array('I', 'M', 'N', 'P', 'T', 'U', 'W') as $alphacol){
			$this->PHPXL->setActiveSheetIndex(0)
				->getStyle($alphacol.$line)
				->getNumberFormat()->setFormatCode('HH:MM')
			;
		}
		foreach (array('K', 'R', 'Y', 'AA') as $alphacol){
			$this->PHPXL->setActiveSheetIndex(0)
				->getStyle($alphacol.$line)
				->getNumberFormat()->setFormatCode('H:MM;@')
			;
		}
		//Set percentage format
		foreach (array('L', 'O', 'S', 'V', 'Z', 'AB') as $alphacol){
			$this->PHPXL->setActiveSheetIndex(0)
				->getStyle($alphacol.$line)
				->getNumberFormat()->setFormatCode('0%')
			;
		}
		
	}
	function setExcelFormulas($line){
		$this->PHPXL->setActiveSheetIndex(0)
			->setCellValue('H'.$line, "=I$line+M$line+P$line+T$line+W$line")//Durée totale du parcours
		
			//E/PRE-Learnings
			->setCellValue('K'.$line, "=(J$line*1.5)*15/360")//Temps en heures
			->setCellValue('L'.$line, "=K$line/I$line")
		
			// Sessions (cours formateur)
			->setCellValue('O'.$line, "=N$line/M$line")
		
			//Microlearnings
			->setCellValue('R'.$line, "=(Q$line*0.083)*15/360")//Timespent formula
			->setCellValue('S'.$line, "=R$line/P$line")//Progression ratio
			
			//Totaux
			->setCellValue('AA'.$line, "=Y$line+U$line+R$line+N$line+K$line")//Total time en heures
			->setCellValue('AB'.$line, "=AA$line/H$line")//Total time en heures
		;
	}
	function definePathType($arrCourses){
		foreach ($arrCourses as $course_id=>$Course){
			if (stripos($Course['course_code'], 'BK_')!==false || stripos($Course['course_name'], 'business key')!==false){
				return 'PROFESSIONNALISER';
			}
			if (stripos($Course['course_name'], 'microlearning - week')!==false || stripos($Course['course_code'], 'ML_W')!==false){
				return 'MAINTENIR';
			}
		}
		if (count($arrCourses)>13)
			return 'PERFECTIONNER';
		return 'DÉCOUVRIR';
	}
}