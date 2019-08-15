<?php

require_once 'classes/manager_need_to_check.php';

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

        return $this->content;
    }
}