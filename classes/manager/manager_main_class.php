<?php

require_once 'data_types/parent_type.php';
require_once 'data_types/course.php';
require_once 'data_types/teacher.php';
require_once 'data_types/item.php';
require_once 'manager_courses_array_getter.php';
require_once 'manager_gui.php';


class ManagerMainClass
{
    private $grades;
    private $courses;

    function __construct($grades)
    {
        $this->grades = $grades;

        $arrayCreater = new ManagerCoursesArrayGetter($this->grades);
        $this->courses = $arrayCreater->get_manager_courses_array();
    }

    public function get_gui() : string 
    {
        $gui = new need_to_check\ManagerGUI($this->courses);
        return $gui->display();
    }
}
