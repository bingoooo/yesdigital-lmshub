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
		
		$finalData = array(
				'DÉCOUVRIR'			=>array(),
				'PERFECTIONNER'		=>array(),
				'PROFESSIONNALISER'	=>array(),
				'MAINTENIR'			=>array(),
		);
		if (!empty($this->_view->data)){
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
			
			$this->_view->data = $finalData;
		}
		else return;
		
		//Building Excel file
		if (empty($_REQUEST['debug'])){
			
			$this->PHPXL = \PHPExcel_IOFactory::load(TPL_ROOT.'/xlsx/bnp.xlsx');
			
			$this->XlActiveSheet = $this->PHPXL->setActiveSheetIndex(0);
			
			$iUser	= 0;
			$line	= 3;
			foreach ($finalData as $pathTypeName=>$PathType){
				foreach ($PathType as $uid=>$User){
					$line++;
					$iUser++;
					
					$this->XlActiveSheet
						->setCellValue('A'.$line, $iUser)
						->setCellValue('B'.$line, !empty($User['firstname']) ? strtoupper($User['lastname']) : trim($User['login'], '/'))
						->setCellValue('C'.$line, strtoupper($User['firstname']))
						->setCellValue('D'.$line, ''/*$uid*/)
						->setCellValue('E'.$line, $pathTypeName)
						->setCellValue('F'.$line, $User['recommended_level'])
						->setCellValue('G'.$line, $this->toExcelDateFormat($User['start_date']))//Date de début de parcours
					;
					
					#PRE-LEARNING
					if (stripos($pathTypeName, 'maintenir')===false){
						$nbDone = $elTimespent = 0;
						if (!empty($User['courses']['EL'])){
							foreach ($User['courses']['EL'] as $EL){
								//Calculate timespent on elearnings
								$elTimespent += (float)$EL['user_course_timespent'];
								//Check EL done checking the dates
								if (!empty($EL['user_course_date_first_access']) && stripos($EL['user_course_date_first_access'], '0000-00-00')===false){
									//Here, the user has at least begun its elearning
									if (!empty($EL['user_course_date_completed']) && stripos($EL['user_course_date_completed'], '0000-00-00')===false)
										$nbDone += 1;	//Completed
									else $nbDone += 0.5;//In progress
								}
							}
						}
						$this->XlActiveSheet
							->setCellValueExplicit('I'.$line, (9 * 60*60)/86400, \PHPExcel_Cell_DataType::TYPE_NUMERIC)//Objectif
							->setCellValue('J'.$line, $nbDone)//Modules réalisés
						;
					}
					else $this->XlActiveSheet->setCellValue('I'.$line, null);
					
					#Cours formateur
					if (stripos($pathTypeName, 'maintenir')===false){
						$nbDone = $timeSpent = $nbSessions = 0;
						if (!empty($User['courses']['SKS'])){
							$nbSessions = count($User['courses']['SKS']);
							foreach ($User['courses']['SKS'] as $session){
								if (!empty($session['user_course_date_completed']) && stripos($session['user_course_date_completed'], '0000-00-00')===false){
									$nbDone++;
									$timeSpent += strtolower($session['course_type'])==='telephone' ? 0.5 : 1;
								}
							}
						}
						$this->XlActiveSheet
							->setCellValueExplicit('M'.$line, (6 * 60*60)/86400, \PHPExcel_Cell_DataType::TYPE_NUMERIC)//Objectif
							->setCellValueExplicit('N'.$line, ($timeSpent * 60*60)/86400, \PHPExcel_Cell_DataType::TYPE_NUMERIC)
						;
					}
					else $this->XlActiveSheet->setCellValue('M'.$line, null);
					
					#Microlearning
					if (stripos($pathTypeName, 'maintenir')===false){
						//if (!empty($User['courses']['ML'])) $microlearning = reset($User['courses']['ML']);
						
						$this->XlActiveSheet
							->setCellValueExplicit('P'.$line, (5 * 60*60)/86400, \PHPExcel_Cell_DataType::TYPE_NUMERIC)//Objectif
							->setCellValue('Q'.$line, 0)//ML réalisés
					 		//->setCellValue('R'.$line, $microlearning['user_course_timespent'])
						;
					}
					else $this->XlActiveSheet->setCellValue('P'.$line, null);
					 
					#Webcoaching
					if (in_array($pathTypeName, array('MAINTENIR', 'PROFESSIONNALISER'))){
						$nbDone = $timeSpent = 0;
						if (!empty($User['courses']['ESP'])){
							foreach ($User['courses']['ESP'] as $session){
								if (!empty($session['user_course_date_completed']) && stripos($session['user_course_date_completed'], '0000-00-00')===false){
									$nbDone++;
									if (strtolower($session['course_type'])==='telephone')
										$timeSpent += (float)0.5;
									else
										$timeSpent += (float)1;
								}
							}
						}
					 	$this->XlActiveSheet
							->setCellValueExplicit('T'.$line, (8 * 60*60)/86400, \PHPExcel_Cell_DataType::TYPE_NUMERIC)//Objectif
							->setCellValueExplicit('U'.$line, ($timeSpent * 60*60)/86400, \PHPExcel_Cell_DataType::TYPE_NUMERIC)
					 	;
					}
					else $this->XlActiveSheet->setCellValue('T'.$line, null);
					 
					 #Ateliers
					if (stripos($pathTypeName, 'profession')!==false){
						$nbDone = $timeSpent = 0;
						if (!empty($User['courses']['BK'])){
							foreach ($User['courses']['BK'] as $session){
								if (!empty($session['user_course_date_completed']) && stripos($session['user_course_date_completed'], '0000-00-00')===false){
									$nbDone++;
									if (strtolower($session['course_type'])==='telephone')
										$timeSpent += (float)0.5;
									else
										$timeSpent += (float)1;
								}
							}
						}
						//$strTime = '0'.(int)$timeSpent.':'.(is_int($timeSpent)? '00' : '30').':00';
					 	$this->XlActiveSheet
							->setCellValueExplicit('W'.$line, (12 * 60*60)/86400, \PHPExcel_Cell_DataType::TYPE_NUMERIC)//Objectif
					 		->setCellValue('X'.$line, $nbDone)
							//->setCellValueExplicit('Y'.$line, ($timeSpent * 60*60)/86400, \PHPExcel_Cell_DataType::TYPE_NUMERIC)
					 	;
					 }
					 else $this->XlActiveSheet->setCellValue('W'.$line, null);
				
					## Formules Excel
					$this->setExcelFormulas($line, $pathTypeName);
					## Formats
					$this->setExcelRowFormat($line);
				}
			}
			$this->setExcelFinalFormat($line);

			## Building the Averages
			$line += 2;
			
			$commonBorderStyle	= array('style'=>\PHPExcel_Style_Border::BORDER_MEDIUM);
			$commonBorders		= array(
				'bottom'=> $commonBorderStyle,
				'left'	=> $commonBorderStyle,
				'right' => $commonBorderStyle,
				'top'	=> $commonBorderStyle
			);
			$commonGrayFIll = array(
				'type'=>\PHPExcel_Style_Fill::FILL_SOLID,
				'color'=>array('rgb' => '808080')
			);
			$commonFont		= array(
				'color'	=> array('rgb'=>'FFFFFF'),
				'bold'	=> true,
			);
			$commonALignment = array('horizontal'=>\PHPExcel_Style_Alignment::HORIZONTAL_CENTER, 'indent'=>0);
			$commonLabelStyle = array(
				'borders'	=> $commonBorders,
				'fill'		=> $commonGrayFIll,
				'alignment'	=> $commonALignment,
				'font'		=> $commonFont
			);
			
			$this->XlActiveSheet->setCellValue('H'.$line, 'MOYENNES :');
			$avgLabel = $this->XlActiveSheet->getStyle('H'.$line);
			$avgLabel->getFont()->setBold(true)->setUnderline(true)->setSize(12);
			$avgLabel->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT)->setIndent(0);
			
			// Big frame
			$this->XlActiveSheet->getStyle('I'.$line.':AB'.($line+3))
				->applyFromArray(array('borders' => $commonBorders))
			;
			
			foreach (array(
				'PACK PROGRESSION'	=>'I'.$line.':S'.$line,
				'PRE-LEARNING'		=>'I'.($line+1).':L'.($line+1),
				'COURS FORMATEUR'	=>'M'.($line+1).':O'.($line+1),
				'MICROLEARNING'		=>'P'.($line+1).':S'.($line+1),
				'COURS WEBCOACHING'	=>'T'.$line.':V'.($line+1),
				'Ateliers Thématiques'=>'W'.$line.':Z'.($line+1),
				'AVANCÉE GLOBALE DU PARCOURS'=>'AA'.$line.':AB'.($line+1),
			) as $label=>$coord){
				$subcoord = substr($coord, 0, strpos($coord, ':'));
				$this->XlActiveSheet->setCellValue($subcoord, $label);
				$this->XlActiveSheet->mergeCells($coord);
				$this->XlActiveSheet->getStyle($coord)->applyFromArray($commonLabelStyle);
			}
			
			foreach (array(
				'I'.($line+2)=>'Objectif (en heures)',
				'J'.($line+2)=>'Modules réalisés',
				'K'.($line+2)=>'Temps passé (en heures)',
				'L'.($line+2)=>'Avancée du pré-learning',
				'M'.($line+2)=>'Objectif (en heures)',
				'N'.($line+2)=>'Cours réalisés (en heures)',
				'O'.($line+2)=>'Avancée des cours formateur',
				'P'.($line+2)=>'Objectif (en heures)',
				'Q'.($line+2)=>'ML réalisés',
				'R'.($line+2)=>'Temps passé (en heures)',
				'S'.($line+2)=>'Avancée du ML',
				'T'.($line+2)=>'Objectif (en heures)',
				'U'.($line+2)=>'Cours réalisés (en heures)',
				'V'.($line+2)=>'Avancée des cours formateur',
				'W'.($line+2)=>'Objectif (en heures)',
				'X'.($line+2)=>'Modules réalisés',
				'Y'.($line+2)=>'Cours réalisés (en heures)',
				'Z'.($line+2)=>'Avancée des cours formateur',
				'AA'.($line+2)=>'Temps de formation réalisé (en heures)',
				'AB'.($line+2)=>'État d\'avancement du parcours',
			) as $coord=>$label){
				$this->XlActiveSheet->setCellValue($coord, $label);
				$this->XlActiveSheet->getStyle($coord)->applyFromArray($commonLabelStyle)
					->getAlignment()->setWrapText(true);
			}
			
			foreach (array('I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB') as $aCol){
				$coord		= $aCol.($line+3);
				$avgcoord	= $aCol.'3:'.$aCol.($line-2);
				//Skip "objectifs" as I don't know how to handle this
				if (!in_array($aCol, array('I', 'M', 'P', 'T', 'W'))){
					$this->XlActiveSheet->setCellValue($coord, '=AVERAGE('.$avgcoord.')');
					$this->XlActiveSheet->getStyle($coord)
						->applyFromArray(array(
							'borders'	=>$commonBorders,
							'alignment'	=>$commonALignment
						))
					;
				}
				// Time format
				if (in_array($aCol, array('I', 'K', 'M', 'N', 'P', 'R', 'T', 'U', 'W', 'Y', 'AA'))){
					$this->XlActiveSheet->getStyle($coord)->getNumberFormat()->setFormatCode('HH:MM');
				}
				// Percent format
				elseif (in_array($aCol, array('L', 'O', 'S', 'V', 'Z', 'AB'))){
					$this->XlActiveSheet->getStyle($coord)->getNumberFormat()->setFormatCode('0%');
				}
				// float(2) by default
				else $this->XlActiveSheet->getStyle($coord)->getNumberFormat()->setFormatCode('# ##0.00');
			}
			$this->XlActiveSheet->getRowDimension($line+2)->setRowHeight(36);
			$this->XlActiveSheet->getRowDimension($line+3)->setRowHeight(18);
			
			$this->sendXlsx('BNP');
		}
	}
	
	function buildDataTree($dbdata){
		if (!empty($dbdata)){
			foreach ($dbdata as $i=>$row){
				if (empty($row['user_id'])) continue;
				$uid = $row['user_id'];
				if (!isset($this->_view->data[$uid])){
					foreach (array('login', 'firstname', 'lastname', 'email', 'recommended_level', 'acquired_level') as $field)
						$this->_view->data[$uid][$field] = isset($row[$field]) ? $row[$field] : null;
				}
				if (!isset($this->_view->data[$uid]['start_date']))
					$this->_view->data[$uid]['start_date'] = null;
				if (
					(!empty($row['user_lp_date_begin_validity']) && !stripos($row['user_lp_date_begin_validity'], '0000:00:00')) ||
					(!empty($row['user_lp_date_assign']) && !stripos($row['user_lp_date_assign'], '0000:00:00'))
					){
					if (empty($row['user_lp_date_begin_validity']) && !empty($row['user_lp_date_assign']))
						$start_date = $row['user_lp_date_assign'];
					elseif (!empty($row['user_lp_date_begin_validity']) && empty($row['user_lp_date_assign']))
						$start_date = $row['user_lp_date_begin_validity'];
					else
						$start_date = ($row['user_lp_date_assign']<$row['user_lp_date_begin_validity']) ? $row['user_lp_date_assign'] : $row['user_lp_date_begin_validity'];
					if (empty($this->_view->data[$uid]['start_date']) || stripos($this->_view->data[$uid]['start_date'], '0000:00:00')!==false || $start_date < $this->_view->data[$uid]['start_date']){
						$this->_view->data[$uid]['start_date'] = $start_date;
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
	
	function setExcelRowFormat($line){
		//Set date format
		$this->XlActiveSheet
			->getStyle('G'.$line)
			->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_DATE_DDMMYYYY)
		;
		//Set time formats
		foreach (array('H') as $alphacol){
			$this->XlActiveSheet
				->getStyle($alphacol.$line)
				->getNumberFormat()->setFormatCode('[H]:MM:SS')
			;
		}
		foreach (array('I', 'M', 'N', 'P', 'T', 'U', 'W') as $alphacol){
			$this->XlActiveSheet
				->getStyle($alphacol.$line)
				->getNumberFormat()->setFormatCode('HH:MM')
			;
		}
		foreach (array('K', 'R', 'Y', 'AA') as $alphacol){
			$this->XlActiveSheet
				->getStyle($alphacol.$line)
				->getNumberFormat()->setFormatCode('H:MM;@')
			;
		}
		//Set percentage format
		foreach (array('L', 'O', 'S', 'V', 'Z', 'AB') as $alphacol){
			$this->XlActiveSheet
				->getStyle($alphacol.$line)
				->getNumberFormat()->setFormatCode('0%')
			;
		}
		
	}
	
	function setExcelFinalFormat($finalrowindex){
		//Set center alignment for columns
		foreach (array('D', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB') AS $aCol){
			$this->XlActiveSheet->getStyle($aCol.'3:'.$aCol.$finalrowindex)
				->getAlignment()
				->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		}
		//Set borders
		foreach (array('A', 'D', 'H', 'L', 'O', 'S', 'V', 'Z', 'AB') as $aCol){
			$this->XlActiveSheet->getStyle($aCol.'3:'.$aCol.$finalrowindex)
				->applyFromArray(
					array(
						'borders' => array(
							'right' => array(
								'style' => \PHPExcel_Style_Border::BORDER_MEDIUM,
							)
						)
					)
				)
			;
		}
		//Border bottom
		$this->XlActiveSheet->getStyle('B'.$finalrowindex.':AB'.$finalrowindex)
			->applyFromArray(
				array(
					'borders' => array(
						'bottom' => array(
							'style' => \PHPExcel_Style_Border::BORDER_MEDIUM,
						)
					)
				)
			)
		;
		
		//2 last cols in bold
		$this->XlActiveSheet->getStyle('AA3:AB'.$finalrowindex)
			->getFont()->setBold(true);
		
	}
	
	function setExcelFormulas($line, $pathTypeName){
		## Everything except MAINTENIR
		if (stripos($pathTypeName, 'maintenir')===false){
			$this->XlActiveSheet
				// E/PRE-Learnings
				->setCellValue('K'.$line, "=(J$line*1.5)*15/360")//Temps en heures
				->setCellValue('L'.$line, "=K$line/I$line")
				
				// Sessions (cours formateur)
				->setCellValue('O'.$line, "=N$line/M$line")
			
				// Microlearnings
				->setCellValue('R'.$line, "=(Q$line*0.083)*15/360")//Timespent formula
				->setCellValue('S'.$line, "=R$line/P$line")//Progression ratio
			;
		}
		if (in_array($pathTypeName, array('MAINTENIR', 'PROFESSIONNALISER'))){
			$this->XlActiveSheet
				//Webcoaching
				->setCellValue('V'.$line, "=U$line/T$line")//Progression ratio
			;
		}
		## Only for "PROFESSIONNALISER"
		if (stripos($pathTypeName, 'profession')!==false){
			$this->XlActiveSheet
				//Ateliers
				->setCellValue('Y'.$line, "=(X$line*1.5)*15/360")//Timespent formula
				->setCellValue('Z'.$line, "=Y$line/W$line")//Progression ratio
			;
		}
		# Totaux
		$this->XlActiveSheet
			->setCellValue('H'.$line, "=I$line+M$line+P$line+T$line+W$line")//Durée totale du parcours
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