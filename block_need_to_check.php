<?php

require_once 'classes/manager/manager_main_class.php';
require_once 'classes/manager/manager_grade_grades_getter.php';
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

        if(has_capability('block/need_to_check:viewmanagergui', context_system::instance()))
        {
            $manager = new ManagerMainClass(ManagerGradeGradesGetter::GLOBAL_MANAGER);
            $this->content->text = $manager->get_gui();
        }
        else if(nlib\is_user_have_manager_role_in_course())
        {
            $manager = new ManagerMainClass(ManagerGradeGradesGetter::LOCAL_MANAGER);
            $this->content->text = $manager->get_gui();
        }


        if(has_capability('block/need_to_check:viewteachergui', context_system::instance()))
        {
            // teacher gui
        }

        

        $this->page->requires->js("/blocks/need_to_check/script.js");

        return $this->content;
    }
}