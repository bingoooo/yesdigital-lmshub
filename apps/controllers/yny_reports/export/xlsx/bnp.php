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
			$iUsers		= array(0, 0, 0, 0);
			$lineStarts	= array(3, 3, 3, 4);
			foreach ($this->_view->data as $uid=>$User){
				### First sort courses and determine learning type
				$elearnings = $microlearnings = $sessions = array();
				$globalTime = 0;
				$sheetIndex = null;
				if (!empty($User['courses'])){
					foreach ($User['courses'] as $course_id=>$Course){
						$globalTime += (int)$Course['user_course_timespent'];
						if ($Course['course_type']==='elearning'){
							if (stripos($Course['course_name'], 'micro')!==false)
								$microlearnings[$course_id] = $Course;
							else
								$elearnings[$course_id] = $Course;
						}
						else
							$sessions[$course_id] = $Course;
						//
						if ($sheetIndex===null){
							if (stripos($Course['course_name'], 'business')!==false){
									
							}
						}
					}
				}
				else continue;
				
				#Determine learning type
				//Is it a "Professionnaliser" ?
				foreach ($User['courses'] as $course_id=>$Course){
					if (stripos($Course['course_name'], 'business')!==false){
						$sheetIndex = 3;
						break;
					}
				}
				//Is it a "Maintenir" ?
				if ($sheetIndex===null){
					if (stripos($Course['course_name'], 'microlearning - week')!==false){
						$sheetIndex = 1;
						break;
					}
				}
				//Is it a "Perfectionner" ?
				if ($sheetIndex===null){
					if (count($User['courses'])>13)
						$sheetIndex = 2;
				}
				//Default, it is a "Decouvrir"
				if ($sheetIndex===null)
					$sheetIndex = 0;
				
				
				$line = $lineStarts[$sheetIndex];
				$iUsers[$sheetIndex]++;
				$iUser = $iUsers[$sheetIndex];
				
				$t_year   = substr($User['user_lp_date_begin_validity'],0,4);
				$t_month  = substr($User['user_lp_date_begin_validity'],5,2);// Fixed problems with offsets
				$t_day    = substr($User['user_lp_date_begin_validity'],7,2);
				$t_date   = \PHPExcel_Shared_Date::FormattedPHPToExcel($t_year, $t_month, $t_day);					
				$this->PHPXL->setActiveSheetIndex($sheetIndex)
					->setCellValue('A'.$line, $iUser)
					->setCellValue('B'.$line, !empty($User['firstname']) ? strtoupper($User['lastname']) : trim($User['login'], '/'))
					->setCellValue('C'.$line, strtoupper($User['firstname']))
					->setCellValue('D'.$line, $uid)
					->setCellValue('E'.$line, $LP['path_name'])
					->setCellValue('F'.$line, $User['recommended_level'])//Date de début de parcours
					->setCellValue('G'.$line, $t_date)//Date de début de parcours
					//->setCellValue('H'.$line, $LP['user_lp_date_end_validity'])//Date de fin de parcours
					->setCellValue('H'.$line, "=I$line+M$line+P$line+T$line+W$line")//Durée totale du parcours
					->setCellValue('I'.$line, '09:00:00')//Objectifs
					->setCellValue('M'.$line, '06:00:00')//Objectifs
					->setCellValue('P'.$line, '05:00:00')//Objectifs
					->setCellValue('T'.$line, '08:00:00')//Objectifs
					->setCellValue('W'.$line, '12:00:00')//Objectifs
				;
				
				if (!empty($elearnings)){
					$nbDone = $elTimespent = 0;
					foreach ($elearnings as $EL){
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
					$this->PHPXL->setActiveSheetIndex($sheetIndex)
						->setCellValue('J'.$line, $nbDone)//Modules réalisés
						//->setCellValue('K'.$line, $strTimespent)//Temps en heures
						->setCellValue('K'.$line, "=(J$line*1,5)*15/360")//Temps en heures
						->setCellValue('L'.$line, "=K$line/I$line")
					;
				}
				if (!empty($sessions)){
					$nbSessions = count($sessions);
					$nbDone = 0;
					foreach ($sessions as $session){
						if (!empty($session['user_course_date_completed']) && stripos($session['user_course_date_completed'], '0000-00-00')===false){
							//It is completed
							$nbDone++;
						}
					}
					$this->PHPXL->setActiveSheetIndex($sheetIndex)
						->setCellValue('N'.$line, "0$nbDone:00")
						->setCellValue('O'.$line, "=N$line/M$line")
					;
				}
				if (!empty($microlearnings)){
					$microlearning = reset($microlearnings);
					$this->PHPXL->setActiveSheetIndex($sheetIndex)
						//->setCellValue('Q'.$line, '')//ML réalisés
						//->setCellValue('R'.$line, $microlearning['user_course_timespent'])
						->setCellValue('R'.$line, "=(Q$line*0,083)*15/360")//Timespent formula
						->setCellValue('S'.$line, "=R$line/P$line")
					;
				}
				
				//TODO Webcoaching + atliers thématiques
				
				//Totaux
				$this->PHPXL->setActiveSheetIndex($sheetIndex)
					->setCellValue('AA'.$line, "=Y$line+U$line+R$line+N$line+K$line")//Total time en heures
					->setCellValue('AB'.$line, "=AA$line/H$line")//Total time en heures
				;
				
				## Formats
				$this->formatExcelRow($line);
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
	
	function formatExcelRow($sheetIndex, $line){
		
		
		### Onglet "Professionnaliser"
		//Set borders
		foreach (array('A', 'D', 'H', 'L', 'O', 'S', 'V', 'Z', 'AB') as $alphacol){
			$this->PHPXL->setActiveSheetIndex($sheetIndex)
				->getStyle($alphacol.$line)->getBorders()->getRight()->setBorderStyle(\PHPExcel_Style_Border::BORDER_MEDIUM)
				->setColor(new \PHPExcel_Style_Color(\PHPExcel_Style_Color::COLOR_BLACK))
			;
		}
		//Set date format
		$this->PHPXL->setActiveSheetIndex($sheetIndex)
			->getStyle('G'.$line)
			->getNumberFormat()->setFormatCode('DD/MM/YYYY')
		;
		//Set time formats
		foreach (array('H') as $alphacol){
			$this->PHPXL->setActiveSheetIndex($sheetIndex)
				->getStyle($alphacol.$line)
				->getNumberFormat()->setFormatCode('[H]:MM:SS')
			;
		}
		foreach (array('I', 'M', 'N', 'P', 'T', 'U', 'W') as $alphacol){
			$this->PHPXL->setActiveSheetIndex($sheetIndex)
				->getStyle($alphacol.$line)
				->getNumberFormat()->setFormatCode('HH:MM')
			;
		}
		foreach (array('K', 'R', 'Y', 'AA') as $alphacol){
			$this->PHPXL->setActiveSheetIndex($sheetIndex)
				->getStyle($alphacol.$line)
				->getNumberFormat()->setFormatCode('H:MM;@')
			;
		}
		//Set percentage format
		foreach (array('L', 'O', 'S', 'V', 'Z', 'AB') as $alphacol){
			$this->PHPXL->setActiveSheetIndex($sheetIndex)
				->getStyle($alphacol.$line)
				->getNumberFormat()->setFormatCode('0%')
			;
		}
		
	}
}