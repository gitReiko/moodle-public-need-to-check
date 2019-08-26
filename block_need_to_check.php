<?php

require_once 'classes/manager/manager_main_class.php';
require_once 'classes/teacher/teacher_main_class.php';
require_once 'classes/grade_grades_getter.php';
require_once 'locallib.php';

use need_to_check_lib as nlib;

class block_need_to_check extends block_base 
{
    
    public function init() 
    {
        $this->title = get_string('need_to_check', 'block_need_to_check');
    }

    public function get_content() 
    {
        $this->content =  new stdClass;
        $this->content->text = '';

        if(has_capability('block/need_to_check:viewmanagergui', context_system::instance()))
        {
            $grades = new GlobalManagerGradeGradesGetter;
            $manager = new ManagerMainClass($grades->get_grades());
            $this->content->text = $manager->get_gui();
        }
        else if(nlib\is_user_have_role_in_course(array('manager')))
        {
            $grades = new LocalManagerGradeGradesGetter;
            $manager = new ManagerMainClass($grades->get_grades());
            $this->content->text = $manager->get_gui();
        }

        if(nlib\is_user_have_role_in_course(array('teacher', 'editingteacher')))
        {
            $grades = new LocalTeacherGradeGradesGetter;
            print_r($grades->get_grades());
            //$teacher = new TeacherMainClass();
            //$this->content->text.= $teacher->get_gui();
        }        

        $this->page->requires->js("/blocks/need_to_check/script.js");

        return $this->content;
    }
}