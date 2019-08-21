<?php

require_once 'data_types/parent_type.php';
require_once 'data_types/course.php';
require_once 'data_types/teacher.php';
require_once 'data_types/item.php';
require_once 'manager_courses_array_creater.php';
require_once 'manager_gui.php';

class ManagerNeedToCheck
{
    private $courses;

    function __construct()
    {
        $ungradedGrades = $this->get_ungraded_grades();

        $arrayCreater = new ManagerCoursesArrayCreater($ungradedGrades);
        $this->courses = $arrayCreater->get_manager_courses_array();
    }

    public function get_gui() : string 
    {
        $gui = new need_to_check\ManagerGUI($this->courses);
        return $gui->display();
    }

    private function get_ungraded_grades()
    {
        $grades = $this->get_ungraded_users();
        // Because grades contains teachers and managers attempts.
        $grades = $this->filter_out_all_non_students($grades);
        return $grades;
    }

    private function get_ungraded_users()
    {
        global $DB;
        // Группировка по itemid приводит к неправильным результатам (вероятно я не умею её использовать правильно)
        $sql = "SELECT gg.id, gg.itemid, gi.itemname, gi.itemmodule, gi.iteminstance, 
                       gg.userid, gg.usermodified, gi.courseid, c.fullname AS coursename, u.firstname 
        FROM {grade_grades} AS gg, {grade_items} AS gi, {course} AS c, {user} AS u
        WHERE gg.userid=gg.usermodified AND gg.finalgrade IS NULL # Select ungraded assign, quiz
            AND gg.itemid= gi.id AND gi.hidden=0 # Add itemname
            AND gg.userid=u.id
            AND gi.courseid=c.id # Add course name
        ORDER BY c.shortname, gi.itemname";

        return $DB->get_records_sql($sql, array());
    }

    private function filter_out_all_non_students(array $grades)
    {
        $studentarchetypes = $this->get_student_archetype_roles();

        foreach($grades as $key => $grade)
        {
            $userroles = get_user_roles(context_course::instance($grade->courseid), $grade->userid);

            if($this->is_user_not_student($userroles, $studentarchetypes))
            {
                unset($grades[$key]);
            }
        }

        return $grades;
    }

    private function get_student_archetype_roles()
    {
        global $DB;
        $conditions = array('archetype'=>'student');
        return $DB->get_records('role', $conditions);
    }

    private function is_user_not_student(array $userroles, array $studentarchetypes) : bool 
    {
        foreach($userroles as $userrole)
        {
            foreach($studentarchetypes as $studentarchetype)
            {
                if($userrole->roleid == $studentarchetype->id) return false;
            }
        }
    
        return true;
    }





}

