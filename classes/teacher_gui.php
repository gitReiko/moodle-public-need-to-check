<?php

class NeedToCheckTeacherGUI 
{
    private $courses;

    function __construct($grades)
    {
        $data = new GuiDataGetter($grades);
        $this->courses = $data->get_manager_courses_array();
    }

    public function get_gui()
    {
        print_r($this->courses);
        return "I'm work!!!";
    }




}
