<?php

require_once 'data_types/parent_type.php';
require_once 'data_types/course.php';
require_once 'data_types/teacher.php';
require_once 'data_types/item.php';
require_once 'forum_grade_grades_getter.php';
require_once 'manager_courses_array_getter.php';
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

        $forum = new ForumUnratedPostsGetter();
        $ungradedGrades = array_merge($ungradedGrades, $forum->get_grades());
        usort($ungradedGrades, "cmp_need_to_check_courses");

        $arrayCreater = new ManagerCoursesArrayGetter($ungradedGrades);
        $this->courses = $arrayCreater->get_manager_courses_array();
    }

    public function get_gui() : string 
    {
        $gui = new need_to_check\ManagerGUI($this->courses);
        return $gui->display();
    }



}

function cmp_need_to_check_courses($a, $b)
{
    return strcmp($a->coursename, $b->coursename);
}