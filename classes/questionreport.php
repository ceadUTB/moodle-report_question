<?php

/**
 * @package   report_questions
 * @copyright 2018 AncaSystems
 * @license   https://github.com/AncaSystems/moodle-questionsreport/blob/master/LICENSE Apache 2.0
 */

namespace report_questions;

defined('MOODLE_INTERNAL') || die();

class questionreport{
  protected $right = 0;
  protected $wrong = 0;
  protected $name;
  protected $times = 0;

  function __construct( $name, $times){
    $this->name = $name;
    $this->times = $times;
  }

  public function rightplusplus(){
    $this->right = $this->right +1;
  }

  public function wrongplusplus(){
    $this->wrong = $this->wrong +1;
  }

  /**
  * get the number of times that question was answered right
  * @return int
  *
  */
  public function get_right(){
    return $this->right;
  }

  /**
  * get the number of times that question was answered wrong
  * @return int
  *
  */
  public function get_wrong(){
    return $this->wrong;
  }

  /**
  * get the number of times that question was show
  * @return int
  *
  */
  public function get_times(){
    return $this->times;
  }

  /**
  * get the number of times that question was show
  * @return string
  *
  */
  public function get_name(){
    return $this->name;
  }

  public function set_times(int $times){
    $this->times = $times;
  }

  /**
  * get the number of times that question was show
  * @return float
  *
  */
  public function RightPercent(){
    if($this->right > 0) return ($this->right / $this->times)*100;
    return 0;
  }

  /**
  * get the number of times that question was show
  * @return float
  *
  */
  public function WrongPercent(){
    if($this->wrong > 0) return ($this->wrong / $this->times)*100;
    return 0;
  }


}
