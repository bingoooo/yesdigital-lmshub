<?php
namespace FragTale\Controller\Yny_Reports\Export\Xlsx;
use FragTale\Controller\Yny_Reports\Export\Xlsx;

/**
 * @author fabrice
 */
class Bnp extends Xlsx{
	
	function main(){
		//Retrieving and sorting data
		$this->_view->data = array();
		$dbdata = $this->retrieveBnpData($this->getDb($this->dbinstancename));
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
			$PHPXlsx = \PHPExcel_IOFactory::load(TPL_ROOT.'/xlsx/bnp.xlsx');
			$line = 3;
			foreach ($this->_view->data as $uid=>$User){
				if (empty($User['learning_plans'])) continue;
				foreach ($User['learning_plans'] as $path_id=>$LP){
					$PHPXlsx->setActiveSheetIndex(0)
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
						$PHPXlsx->setActiveSheetIndex(0)
							->setCellValue('G'.$line, '')//Objectif en heures
							->setCellValue('H'.$line, $lsat['module_done'].'/'.$lsat['module_count'])
							->setCellValue('I'.$line, $globalTime)
							->setCellValue('J'.$line, $lsat['module_done']!=$lsat['module_count']?'Incomplete':'Complete')
						;
					if (!empty($microlearnings)){
						$microlearning = reset($microlearnings);
						$PHPXlsx->setActiveSheetIndex(0)
							->setCellValue('N'.$line, '')//Objectif en heures
							//->setCellValue('O'.$line, '')//ML rélaisés
							->setCellValue('P'.$line, $microlearning['user_course_timespent'])
							//->setCellValue('Q'.$line, '')//Avancée du microlearning
						;
					}
					/*if (!empty($elearnings))
						$PHPXlsx->setActiveSheetIndex(0)
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
						$PHPXlsx->setActiveSheetIndex(0)
							->setCellValue('K'.$line, '')//Objectif en heures
							->setCellValue('L'.$line, $nbDone.'/'.$nbSessions)
							->setCellValue('M'.$line, $nbDone!=$nbSessions?'Incomplete':'Complete')
						;
					}
					
					$PHPXlsx->setActiveSheetIndex(0)
						->setCellValue('R'.$line, $globalTime)//Total time en heures
					;
					$line++;
				}
			}
			// Redirect output to a client’s web browser (Excel2007)
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="bnp-'.date('Ymd_H-i').'.xlsx"');
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
	
	function retrieveBnpData(\FragTale\Db\Adapter $Dba){
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