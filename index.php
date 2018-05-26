<?php

/**
 * @package   report_questions
 * @copyright 2018 AncaSystems
 * @license   https://github.com/AncaSystems/moodle-questionsreport/blob/master/LICENSE Apache 2.0
 */


require(__DIR__ . '/../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot . '/report/questions/questions_table.php');
require_once($CFG->dirroot. '/report/questions/lib.php');


$courseid = required_param('id', PARAM_INT);

$questionresponse = array();
$questionReport = array();
$questions_aux;

$context = context_course::instance($courseid);
$type = optional_param('type', 'global',PARAM_TEXT);
$download = optional_param('download', '', PARAM_ALPHA);

require_capability('mod/quiz:grade', $context);
$viewCap  = has_capability('quiz/questionsreport:view', $context);

if ($viewCap) {
      global $DB;
      $curso = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
      require_login($curso);
      
      $options = array('id' => $courseid,'type' => $type);
      $reporturl = new moodle_url('/report/questions/index.php', $options);

      $PAGE->set_url('/report/questions/index.php',$options);
      $PAGE->set_pagelayout('report');
      $PAGE->set_title(get_string('questions:componentname', 'report_questions'));
      $PAGE->set_heading(get_string('questions:componentname', 'report_questions'));
      
      $table = new report_questions_table();



      // Table setup
      $table->questions_setup($courseid, $reporturl);
      
      $courseshortname = format_string($curso->shortname,true,
                                        array('context' => $context));
      $filename = get_string('questions:componentname','report_questions').' '.$courseshortname;

      $table->is_downloading($download,$filename,
                                   get_string('questions:componentname', 'report_questions'));


       //Global Questions
      $questions = $DB->get_records_sql('SELECT q.id,q.qtype,q.name from {question} q 
                                                INNER JOIN {question_categories} qc ON qc.contextid = '.$context->id.' 
                                                WHERE q.category = qc.id');
      //Quices
      $quices = $DB->get_records_sql('SELECT q.id FROM {quiz} q WHERE q.course = '.$courseid);

      switch ($type){
        case 'students': 
              // get all students
              $students = get_role_users(5, $context, true);
              //Catching attempts and responses
              if(sizeof($students) > 0){
                foreach($students as $student){
                  foreach($quices as $quiz){
                    $response = $DB->get_records_sql('SELECT
                                                         DISTINCT(qas.userid), qas.state AS response, q.name AS question_name, q.id AS question_id
                                                      FROM mdl_quiz_attempts quiza
                                                      JOIN mdl_question_usages qu ON qu.id = quiza.uniqueid
                                                      JOIN mdl_question_attempts qa ON qa.questionusageid = qu.id
                                                      JOIN mdl_question_attempt_steps qas ON qas.questionattemptid = qa.id
                                                      JOIN mdl_question q ON q.id = qa.questionid
                                                      LEFT JOIN mdl_question_attempt_step_data qasd ON qasd.attemptstepid = qas.id
                                                      
                                                      WHERE quiza.quiz = '.$quiz->id.' AND qas.state IN ("gradedwrong", "gradedright") AND quiza.userid ='.$student->id);
                    if (sizeof($response)>0) {
                      foreach ($response as $data) { 
                            if (array_key_exists($data->question_id,$questionresponse)) {
                              array_push($questionresponse[$data->question_id]['data'], $data->response );
                            }else{
                              $questionresponse[$data->question_id] = array();
                              array_push($questionresponse[$data->question_id], array('name' => $data->question_name, 'data' => $data->response) );
                            }
                      }
                    }
                  }
                foreach ($questions as $question) {
                  if (!array_key_exists($question->id,$questionresponse)) {
                    $questionresponse[$question->id] = array();
                    array_push($questionresponse[$data->id], array('name' => $question->name, 'data' => array("gradedwrong" => 0,"gradedright" => 0)) );
                  }
                }
                }
              }else{
                redirect(new moodle_url($reporturl));
              }
              break;
        case 'global':
            foreach($quices as $quiz){
              $response = $DB->get_records_sql('SELECT
                                                  DISTINCT(qas.userid), qas.state AS response, q.name AS question_name
                                                FROM mdl_quiz_attempts quiza
                                                JOIN mdl_question_usages qu ON qu.id = quiza.uniqueid
                                                JOIN mdl_question_attempts qa ON qa.questionusageid = qu.id
                                                JOIN mdl_question_attempt_steps qas ON qas.questionattemptid = qa.id
                                                JOIN mdl_question q ON q.id = qa.questionid
                                                LEFT JOIN mdl_question_attempt_step_data qasd ON qasd.attemptstepid = qas.id
                                                
                                                WHERE quiza.quiz = '.$quiz->id.' AND qas.state IN ("gradedwrong", "gradedright")');
              if (sizeof($response)>0) {
                foreach ($response as $data) { 
                      if (array_key_exists($data->question_name,$questionresponse)) {
                      
                        array_push($questionresponse[$data->question_name], $data->response );
                      }else{
                        $questionresponse[$data->question_name] = array();
                        array_push($questionresponse[$data->question_name], $data->response );
                      }
                }
              }
            }
            foreach ($questions as $question) {
              if (!array_key_exists($question->name,$questionresponse)) {
                $questionresponse[$question->name] = array();
                array_push($questionresponse[$data->name], array("gradedwrong" => 0,"gradedright" => 0));
              }
            }
            break;
        default:
              redirect(new moodle_url($reporturl));
              break;
      }

      foreach ($questionresponse as $questionid => $questiondata) {
        array_push($questionReport, QuestionReport($questiondata['data'],$questionid, $questiondata['name']));
      }


      if ($table->is_downloading()) {
        download_questions_report_table($table,$questionReport);
        $table->export_class_instance()->finish_document();
      }else{
          if (!$table->is_downloading()) {
            echo $OUTPUT->header();
          }
          switch($type){
            case 'global':
                $reporturlnav = new moodle_url('/report/questions/index.php', array('id' => $courseid,'type' => 'students'));
                break;
            case 'students':
                $reporturlnav = new moodle_url('/report/questions/index.php', array('id' => $courseid,'type' => 'global'));
                break;
          } 
         echo show_navs($reporturlnav,$type);
         output_question_report_data($table,$questionReport);
         echo everything_download_options($reporturl);
         echo $OUTPUT->footer();
      }
      

     
}else {
  redirect(new moodle_url('/my/'));
}
