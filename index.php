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
$download = optional_param('download', '', PARAM_ALPHA);

require_capability('mod/quiz:grade', $context);
$viewCap  = has_capability('quiz/questionsreport:view', $context);

if ($viewCap) {
      global $DB;
      $curso = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
      require_login($curso);
      
      $options = array('id' => $courseid);
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
       // Quiz Questions
     $questions = $DB->get_records_sql('SELECT q.id,q.qtype from {question} q 
                                                INNER JOIN {question_categories} qc ON qc.contextid = '.$context->id.' 
                                                WHERE q.category = qc.id');
      

      // get all students
      $students = get_role_users(5, $context, true);

      //Catching attempts and responses
      foreach ($questions as $question) {
        foreach ($students as $student) {
          $response = $DB->get_records_sql('SELECT qas.state as response, q.name AS question_name FROM {question_attempt_steps} qas
            JOIN {question_attempts} qa ON qa.id = qas.questionattemptid
            JOIN {question} q ON q.id = '.$question->id.'
            WHERE qas.userid = '. $student->id .' AND qas.state IN ("gradedwrong", "gradedright") GROUP BY qas.state ORDER BY q.id ');
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
      }

      foreach ($questionresponse as $questionname => $questiondata) {
        array_push($questionReport, $questionReport($questiondata,$questionname));
      }


      if ($table->is_downloading()) {
        download_questions_report_table($table,$questionReport);
        $table->export_class_instance()->finish_document();
      }else{
          if (!$table->is_downloading()) {
            echo $OUTPUT->header();
          }
         output_question_report_data($questionReport);
         echo everything_download_options($reporturl);
         echo $OUTPUT->footer();
      }
      

     
}else {
  redirect(new moodle_url('/'));
}
