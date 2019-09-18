<?php

require_once 'classes/manager_gui.php';
require_once 'classes/teacher_gui.php';
require_once 'classes/database_teacher_table_manager.php';
require_once 'classes/grade_grades_getter.php';
require_once 'classes/gui_data_getter.php';
require_once 'classes/data_types/parent_type.php';
require_once 'classes/data_types/course.php';
require_once 'classes/data_types/teacher.php';
require_once 'classes/data_types/item.php';
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

        /*
        if(has_capability('block/need_to_check:viewmanagergui', context_system::instance()))
        {
            $grades = new GlobalManagerGradeGradesGetter;
            $manager = new NeedToCheckManagerGUI($grades->get_grades());
            $this->content->text = $manager->get_gui();
        }
        else if(nlib\is_user_have_role_in_course_module(array('manager')))
        {
            $grades = new LocalManagerGradeGradesGetter;
            $manager = new NeedToCheckManagerGUI($grades->get_grades());
            $this->content->text = $manager->get_gui();
        }

        if(nlib\is_user_have_role_in_course_module(array('teacher', 'editingteacher')))
        {
            $grades = new LocalTeacherGradeGradesGetter;
            $teacher = new NeedToCheckTeacherGUI($grades->get_grades());
            $this->content->text.= $teacher->get_gui();
        }
        */   
        $searcher = new DatabaseTeacherTableManager();
        $searcher->update_teachers_table();   

        $this->page->requires->js("/blocks/need_to_check/script.js");

        return $this->content;
    }

    function has_config() 
    {
        return true;
    }
}