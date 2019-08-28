<?php

class CheckingItem extends ParentType
{

    // params neccessary for gui
    private $link;

    // technical params
    private $itemmodule;
    private $iteminstance;
    private $studentid;
    private $checkTime;

    // Item types
    const assign = 'assign';
    const forum = 'forum';
    const quiz = 'quiz';

    function __construct(stdClass $grade) 
    {
        $this->checkTime = get_config('block_need_to_check', 'check_time');

        $this->id = $grade->itemid;
        $this->name = $grade->itemname;
        $this->itemmodule = $grade->itemmodule;
        $this->iteminstance = $grade->iteminstance;
        $this->studentid = $grade->userid;
        $this->timefinish = $this->get_item_timefinish($grade);
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
        $this->timefinish = $this->get_item_timefinish($grade);

        if($this->is_check_time_has_expired())
        {
            $this->expiredWorksCount++;
        }
    }

    private function get_item_link() : string
    {
        if($this->itemmodule == self::assign)
        {
            return $this->get_assign_link();
        }
        else if($this->itemmodule == self::forum)
        {
            return $this->get_forum_link();
        }
        else if($this->itemmodule == self::quiz)
        {
            return $this->get_quiz_link();
        }
        else
        {
            return '';
        }
    }

    private function get_assign_link() : string 
    {
        $cm = get_coursemodule_from_instance('assign', $this->iteminstance);
        return "/mod/assign/view.php?action=grading&id={$cm->id}";   
    }

    private function get_forum_link() : string 
    {
        $cm = get_coursemodule_from_instance('forum', $this->iteminstance);
        return "/mod/forum/view.php?id={$cm->id}";   
    }

    private function get_quiz_link() : string
    {
        $cm = get_coursemodule_from_instance('quiz', $this->iteminstance);
        return "/mod/quiz/report.php?id={$cm->id}&mode=overview";
    }

    private function get_item_timefinish($grade) 
    {
        if($this->itemmodule == self::assign)
        {
            return $this->get_assign_timefinish();
        }
        else if($this->itemmodule == self::forum)
        {
            return $grade->timefinish;
        }
        else if($this->itemmodule == self::quiz)
        {
            return $this->get_quiz_attempt_timefinish();
        }
        else
        {
            return 0;
        }
    }

    private function get_assign_timefinish()
    {
        global $DB;
        $conditions = array('assignment'=>$this->iteminstance, 'userid'=>$this->studentid, 'latest'=>1);
        $assignAttempt = $DB->get_record('assign_submission', $conditions);
        return $assignAttempt->timemodified;
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
        if(($this->timefinish + $this->checkTime) < time()) return true;
        else return false;
    }


}