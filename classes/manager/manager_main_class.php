<?php

require_once 'data_types/parent_type.php';
require_once 'data_types/course.php';
require_once 'data_types/teacher.php';
require_once 'data_types/item.php';
require_once 'manager_courses_array_creater.php';
require_once 'manager_grade_grades_getter.php';
require_once 'manager_gui.php';

class ManagerMainClass
{
    private $managerType;
    private $courses;

    function __construct(string $managerType)
    {
        $this->managerType = $managerType;

        $grades = new ManagerGradeGradesGetter($this->managerType);
        $ungradedGrades = $grades->get_grades();

        $arrayCreater = new ManagerCoursesArrayCreater($ungradedGrades);
        $this->courses = $arrayCreater->get_manager_courses_array();
    }

    public function get_gui() : string 
    {
        $gui = new need_to_check\ManagerGUI($this->courses);
        return $gui->display();
    }



}

