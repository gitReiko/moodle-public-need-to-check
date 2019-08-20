<?php

class CheckingItem extends ParentType
{

    // params neccessary for gui
    private $link;

    // technical params
    private $itemmodule;
    private $iteminstance;
    private $studentid;

    const checkTime = 604800; // 7 days

    // Item types
    const quiz = 'quiz';

    function __construct(stdClass $grade) 
    {
        $this->id = $grade->itemid;
        $this->name = $grade->itemname;
        $this->itemmodule = $grade->itemmodule;
        $this->iteminstance = $grade->iteminstance;
        $this->studentid = $grade->userid;
        $this->timefinish = $this->get_item_timefinish();
        $this->link = $this->get_item_link();
        $this->uncheckedWorksCount = 1;

        if($this->is_check_time_has_expired())
        {
            $this->expiredWorksCount = 1;
        }
        else
        {
            $this->expiredWorksCount = 0;
        }
    }

    public function get_link() : string 
    {
        return $this->link;
    }

    public function update_works_count(stdClass $grade) : void 
    {
        $this->uncheckedWorksCount++;

        $this->studentid = $grade->userid;
        $this->timefinish = $this->get_item_timefinish();

        if($this->is_check_time_has_expired())
        {
            $this->expiredWorksCount++;
        }
    }

    private function get_item_link() : string
    {
        if($this->itemmodule == self::quiz)
        {
            return $this->get_quiz_link();
        }
        else
        {
            return '';
        }
    }

    private function get_quiz_link() : string
    {
        $cm = get_coursemodule_from_instance('quiz', $this->iteminstance);
        return "/mod/quiz/report.php?id={$cm->id}&mode=overview";
    }

    private function get_item_timefinish() 
    {
        if($this->itemmodule == self::quiz)
        {
            return $this->get_quiz_attempt_timefinish();
        }
        else
        {
            return 0;
        }
    }

    private function get_quiz_attempt_timefinish()
    {
        global $DB;
        $lastattempt = $this->get_quiz_last_attempt();
        $conditions = array('quiz'=>$this->iteminstance, 'userid'=>$this->studentid, 'attempt'=>$lastattempt);
        $quizAttempt = $DB->get_record('quiz_attempts', $conditions);
        return $quizAttempt->timefinish;
    }

    private function get_quiz_last_attempt()
    {
        global $DB;
        $conditions = array('quiz'=>$this->iteminstance, 'userid'=>$this->studentid);
        return $DB->count_records('quiz_attempts', $conditions);
    }

    private function is_check_time_has_expired() : bool 
    {
        if(($this->timefinish + self::checkTime) < time()) return true;
        else return false;
    }


}