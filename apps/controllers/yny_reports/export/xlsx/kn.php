<?php
namespace FragTale\Controller\Yny_Reports\Export\Xlsx;
use FragTale\Controller\Yny_Reports\Export\Xlsx;

/**
 * @author fabrice
 */
class Kn extends Xlsx{
	
	function main(){
		//Retrieving and sorting data
		$this->_view->data = array();
		$dbdata = $this->retrieveKnData($this->getDb($this->dbinstancename));
		if (!empty($dbdata)){
			foreach ($dbdata as $i=>$row){
				if (empty($row['user_id'])) continue;
				$uid = $row['user_id'];
				if (!isset($this->_view->data[$uid])){
					foreach (array('login', 'firstname', 'lastname', 'email', 'recommended_level', 'acquired_level') as $field)
						$this->_view->data[$uid][$field] = isset($row[$field]) ? $row[$field] : null;
				}
				if (!empty($row['path_id'])){
					$path_id = (int)$row['path_id'];
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
						if (!isset($this->_view->data[$uid]['learning_plans'][$path_id]['courses'][$course_id])){
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
							) as $field)
								$this->_view->data[$uid]['learning_plans'][$path_id]['courses'][$course_id][$field] = isset($row[$field]) ? $row[$field] : null;
						}
					}
				}
			}
		}
		//Building Excel file
		if (!empty($this->_view->data)){
			require_once LIB_ROOT.'/PHPExcel.php';
			$PHPXlsx = \PHPExcel_IOFactory::load(TPL_ROOT.'/xlsx/kn.xlsx');
			$line = 2;
			foreach ($this->_view->data as $uid=>$User){
				if (empty($User['learning_plans'])) continue;
				foreach ($User['learning_plans'] as $path_id=>$LP){
					$lpstartdate= (stripos($LP['user_lp_date_begin_validity'], '0000-00-00')!==false || empty($LP['user_lp_date_begin_validity'])) ? '' : date('d/m/Y', strtotime($LP['user_lp_date_begin_validity'])); 
					$lpenddate	= (stripos($LP['user_lp_date_end_validity'], '0000-00-00')!==false || empty($LP['user_lp_date_end_validity'])) ? '' : date('d/m/Y', strtotime($LP['user_lp_date_end_validity'])); 
					$PHPXlsx->setActiveSheetIndex(0)
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
							$PHPXlsx->setActiveSheetIndex(0)
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
								$PHPXlsx->setActiveSheetIndex(0)
									->setCellValue($courseColumns[$iCol].$line, $Course['module_status'])
									->setCellValue($courseColumns[$iCol+1].$line, $completiondate)
								;
								$iCol+=2;
							}
						}
						if (!empty($sessions)){
							$PHPXlsx->setActiveSheetIndex(0)
								->setCellValue('AC'.$line, count($sessions))
								->setCellValue('AD'.$line, $nbSessionPassed)
							;
						}
						if (!empty($microlearnings)){
							$Course = reset($microlearnings);
							$mlStatus = (!empty($Course['user_course_date_completed']) && stripos($Course['user_course_date_completed'], '0000-00-00')===false) ? 'Completed' :
											(empty($Course['user_course_date_first_access']) || stripos($Course['user_course_date_first_access'], '0000-00-00')!==false ? 'Not started' : 'In progress');
							$PHPXlsx->setActiveSheetIndex(0)
								->setCellValue('AA'.$line, $mlStatus)
								->setCellValue('AM'.$line, '')//Completion of ML
							;
						}
					}
					//$completion
					$lastAccess = (stripos($lastAccess, '0000-00-00')!==false || empty($lastAccess)) ? '' : date('d/m/Y', strtotime($lastAccess)); 
					$PHPXlsx->setActiveSheetIndex(0)
						->setCellValue('L'.$line, $nbElearnings)//Amount of modules in learning plan
						->setCellValue('AE'.$line, $globalTime)//Total time en heures
						->setCellValue('AF'.$line, $lastAccess)
					;
					$line++;
				}
			}
			// Redirect output to a client’s web browser (Excel2007)
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="k&n-'.date('Ymd_H-i').'.xlsx"');
			header('Cache-Control: max-age=0');
			// If you're serving to IE 9, then the following may be needed
			//header('Cache-Control: max-age=1');
			
			// If you're serving to IE over SSL, then the following may be needed
			//header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
			header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
			header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
			header ('Pragma: public'); // HTTP/1.0
			
			$objWriter = \PHPExcel_IOFactory::createWriter($PHPXlsx, 'Excel2007');
			$objWriter->save('php://output');
			exit;
		}
	}
	
	function retrieveKnData(\FragTale\Db\Adapter $Dba){
		$query =
			'SELECT DISTINCT '.
				
				'UI.*, '.
				
				'RL.value AS recommended_level, '.
				'AL.value AS acquired_level, '.
				
				'CI.course_id, '.
				'CI.code AS course_code, '.
				'CI.course_name, '.
				'CI.course_txt, '.
				'CI.img_url AS course_image, '.
				'CI.course_language, '.
				'CI.status AS course_status, '.
				'CI.course_type, '.
				'CI.sub_start_date AS course_sub_start_date, '.
				'CI.sub_end_date AS course_sub_end_date, '.
				'CI.date_begin AS course_date_begin, '.
				'CI.date_end AS course_date_end, '.
				'CI.course_link, '.
				'CI.course_category_name, '.
				'CI.course_label, '.
				'CI.certificate_enable AS course_certificate_enable, '.
				'CI.certificate_name AS course_certificate_name, '.
				
				'UC.date_inscripted AS user_course_date_inscripted, '.
				'UC.date_first_access AS user_course_date_first_access, '.
				'UC.date_last_access AS user_course_date_last_access, '.
				'UC.date_completed AS user_course_date_completed, '.
				'UC.status AS user_course_status, '.
				'UC.waiting AS user_course_waiting, '.
				'UC.score_given AS user_course_score, '.
				'UC.timespent AS user_course_timespent, '.
				
				'LPI.*, '.
				'ULP.course_completed AS user_lp_completed, '.
				'ULP.date_assign AS user_lp_date_assign, '.
				'ULP.date_begin_validity AS user_lp_date_begin_validity, '.
				'ULP.date_end_validity AS user_lp_date_end_validity, '.
				'ULP.catchup_user_limit AS user_lp_catchup_limit, '.
				'ULP.timespent AS user_lp_timespent '.
				
			'FROM UserInfo UI '.
				'INNER JOIN BranchUsers BU ON BU.user_id = UI.user_id '.
				'INNER JOIN UserCourses UC ON UI.user_id = UC.user_id '.
				'INNER JOIN CourseInfo CI ON CI.course_id = UC.course_id '.
				'INNER JOIN LearningPlanCourses LPC ON LPC.course_id = CI.course_id '.
				'INNER JOIN LearningPlanInfo LPI ON LPI.path_id = LPC.path_id '.
				//'LEFT JOIN UserLearningPlans ULP ON ULP.path_id = LPI.path_id AND ULP.user_id = UI.user_id '.
				'INNER JOIN UserLearningPlans ULP ON ULP.path_id = LPI.path_id AND ULP.user_id = UI.user_id '.
				"LEFT JOIN UserAdditionalInfo RL ON RL.user_id = UI.user_id AND RL.attribute like '%recommended%' ".
				"LEFT JOIN UserAdditionalInfo AL ON AL.user_id = UI.user_id AND AL.attribute like '%acquired%' ".
				//'WHERE BU.branch_id =  '.
			'ORDER BY UI.lastname ASC, UI.firstname ASC, LPI.path_id, CI.course_id ASC '.
			'LIMIT 0, 1000';
		return $Dba->getTable($query);
	}
}