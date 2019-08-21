<?php

require_once 'classes/manager/manager_need_to_check.php';

class block_need_to_check extends block_base 
{
    
    public function init() 
    {
        $this->title = get_string('need_to_check', 'block_need_to_check');
    }

    public function get_content() 
    {
        $this->content =  new stdClass;
        $manager = new ManagerNeedToCheck();

        $this->content->text = $manager->get_gui();

        $this->page->requires->js("/blocks/need_to_check/script.js");

        return $this->content;
    }
}