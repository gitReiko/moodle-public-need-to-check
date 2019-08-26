<?php

require_once 'data_types/parent_type.php';
require_once 'data_types/course.php';
require_once 'data_types/teacher.php';
require_once 'data_types/item.php';
require_once 'forum_grade_grades_getter.php';
require_once 'manager_courses_array_getter.php';
require_once 'manager_gui.php';


class ManagerMainClass
{
    private $grades;
    private $courses;

    function __construct($grades)
    {
        $this->grades = $grades;

        $forum = new ForumUnratedPostsGetter();
        $this->grades = array_merge($this->grades, $forum->get_grades());
        usort($this->grades, "cmp_need_to_check_courses");

        $arrayCreater = new ManagerCoursesArrayGetter($this->grades);
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