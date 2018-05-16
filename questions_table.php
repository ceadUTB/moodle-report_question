<?php
/**
 * @package   report_questions
 * @copyright 2018 AncaSystems
 * @license   https://github.com/AncaSystems/moodle-questionsreport/blob/master/LICENSE Apache 2.0
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/tablelib.php');

class report_questions_table extends flexible_table{
  protected $report;
  protected $cmid;

  public function __construct()  {
    parent::__construct('mod-report-report-questions-report');
  }


  public function questions_setup($courseid, $reporturl){
    $this->$courseid = $courseid;

    $columns = array('name','times', 'right','rightpercent', 'wrong', 'wrongpercent');
    $headers = array(get_string('question_name','report_questions'),
                                     get_string('question_times','report_questions'),
                                     get_string('question_right','report_questions'),
                                     get_string('question_rightpercent','report_questions'),
                                     get_string('question_wrong','report_questions'),
                                     get_string('question_wrongpercent','report_questions'));


    $this->define_columns($columns);
    $this->define_headers($headers);
    $this->sortable(false);

    $this->define_baseurl($reporturl->out());

    $this->collapsible(true);

    parent::setup();
  }

  protected function col_name($QuestionReport){
    $name = $QuestionReport->get_name();
    if ($this->is_downloading()) {
      return $name;
    }
    return $name;
  }

  protected function col_times($QuestionReport){
    $times = $QuestionReport->get_times();
    if ($this->is_downloading()) {
      return $times;
    }
    return $times;
  }

  protected function col_right($QuestionReport){
    $right = $QuestionReport->get_right();
    if ($this->is_downloading()) {
      return $right;
    }
    return $right;
  }

  protected function col_rightpercent($QuestionReport){
    $rightpercent = $QuestionReport->RightPercent() . '%';
    if ($this->is_downloading()) {
      return $rightpercent;
    }
    return $rightpercent;
  }

  protected function col_wrong($QuestionReport){
    $wrong = $QuestionReport->get_wrong();
    if ($this->is_downloading()) {
      return $wrong;
    }
    return $wrong;
  }

  protected function col_wrongpercent($QuestionReport){
    $wrongpercent = $QuestionReport->WrongPercent() . '%';
    if ($this->is_downloading()) {
      return $wrongpercent;
    }
    return $wrongpercent;
  }
}
