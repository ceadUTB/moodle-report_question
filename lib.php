<?php

/**
 * @package   report_questions
 * @copyright 2018 AncaSystems
 * @license   https://github.com/AncaSystems/moodle-questionsreport/blob/master/LICENSE Apache 2.0
 */

defined('MOODLE_INTERNAL') || die();

/**
* Model translate Function
*/
function QuestionReport($questiondata, $id ,$name){
  $QuestionReport = new \report_questions\questionreport($name,sizeof($questiondata));
  foreach ($questiondata as $data) {
    if ($data == "gradedwrong") {
      $QuestionReport->wrongplusplus();
    }else if($data == "gradedright"){
      $QuestionReport->rightplusplus();
    }
  }

  return $QuestionReport;
}

/**
* Table Function
*/
function output_question_report_data($table,$QuestionsReports){
  global $OUTPUT;
  $questioninfotable = new html_table();
  $questioninfotable->aling = array('center','center');
  $questioninfotable->width = '60%';
  $questioninfotable->attributes['class'] = 'table table-bordered titlesleft';

  $questioninfotable->head = array(get_string('question_name','report_questions'),
                                   get_string('question_times','report_questions') . '(<i class="fa fa-clock-o times-color" aria-hidden="true"></i>)',
                                   get_string('question_right','report_questions') . '(<i class="fa fa-check right-color" aria-hidden="true"></i>)',
                                   get_string('question_rightpercent','report_questions') . '(<span class="percent-color"><b>%</b></span>)',
                                   get_string('question_wrong','report_questions') . '(<i class="fa fa-times wrong-color" aria-hidden="true"></i>)',
                                   get_string('question_wrongpercent','report_questions') . '(<span class="percent-color"><b>%</b></span>)');

  $questioninfotable->data = array();
  foreach ($QuestionsReports as $QuestionReport) {
    $datumfromtable = $table->format_row($QuestionReport);
    $questioninfotable->data[] = $datumfromtable;
  }
  echo $OUTPUT->heading(get_string('questions:componentname', 'report_questions'), 3);
  echo html_writer::table($questioninfotable);
}

/**
* Download button
*/
function everything_download_options(moodle_url $reporturl){
  global $OUTPUT;
  return $OUTPUT->download_dataformat_selector(get_string('export', 'report_questions'),$reporturl->out_omit_querystring(), 'download', $reporturl->params());
}


/**
* Download Function
*/
function download_questions_report_table($table,$QuestionsReports){
  if ($table->is_downloading() == 'html') {
    echo output_question_report_data($table,$QuestionsReports);
    return;
  }

  $exportclass = $table->export_class_instance();
  $exportclass->start_table(get_string('questions:componentname', 'report_questions'));
  $exportclass->output_headers($table->headers);

  foreach ($QuestionsReports as $QuestionReport) {
    $row = array();
    foreach ($table->format_row($QuestionReport) as $heading => $value) {
      $row[] = $value;
    }
    $exportclass->add_data($row);
  }

  $exportclass->finish_table();
}


function report_questions_extend_navigation_course($navigation, $course, $context) {
    if (has_capability('report/questionsreport:view', $context)) {
        $url = new moodle_url('/report/questions/index.php', array('id'=>$course->id));
        $navigation->add(get_string('pluginname', 'report_questions'), $url, navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', ''));
    }
}

function show_navs($url,$type){
  $nav = html_writer::start_tag('ul', array('class'=>"nav nav-tabs"));
  if($type == "global"){
    $nav.= html_writer::start_tag('li', array('class'=>'active'));
    $nav.= html_writer::link($url,'Global');
    $nav.= html_writer::end_tag('li');
    $nav.= html_writer::start_tag('li');
    $nav.= html_writer::link($url,'Estudiantes');
    $nav.= html_writer::end_tag('li');
  }else if($type == "students"){
    $nav.= html_writer::start_tag('li');
    $nav.= html_writer::link($url,'Global');
    $nav.= html_writer::end_tag('li');
    $nav.= html_writer::start_tag('li',array('class'=>'active'));
    $nav.= html_writer::link($url,'Estudiantes');
    $nav.= html_writer::end_tag('li');
  }
  $nav.= html_writer::end_tag('ul');

  return $nav;
}