<?php

use need_to_check_lib as nlib;

class ManagerGradeGradesGetter 
{
    private $managerType;

    const LOCAL_MANAGER = 'local_manager'; // manager in course
    const GLOBAL_MANAGER = 'global_manager';

    function __construct(string $managerType)
    {
        $this->managerType = $managerType;
    }

    public function get_grades()
    {
        if($this->managerType == self::GLOBAL_MANAGER)
        {
            $grades = $this->get_ungraded_users_for_global_manager();
        }
        else if($this->managerType == self::LOCAL_MANAGER)
        {
            $grades = $this->get_ungraded_users_for_local_manager();
        }

        $grades = $this->filter_out_all_non_student_users($grades);
        return $grades;
    }

    private function get_ungraded_users_for_global_manager()
    {
        
        $sql = "SELECT gg.id, gg.itemid, gi.itemname, gi.itemmodule, gi.iteminstance, 
                       gg.userid, gg.usermodified, gi.courseid, c.fullname AS coursename, u.firstname 
        FROM {grade_grades} AS gg, {grade_items} AS gi, {course} AS c, {user} AS u
        WHERE gg.userid=gg.usermodified AND gg.finalgrade IS NULL # Select ungraded assign, quiz
            AND gg.itemid= gi.id AND gi.hidden=0 # Add itemname
            AND gg.userid=u.id
            AND gi.courseid=c.id # Add course name
        ORDER BY c.shortname, gi.itemname";

        global $DB;
        return $DB->get_records_sql($sql, array());
    }

    private function filter_out_all_non_student_users(array $grades)
    {
        $studentArchetypes = nlib\get_archetypes_roles(array('student'));

        foreach($grades as $key => $grade)
        {
            $userRoles = get_user_roles(\context_course::instance($grade->courseid), $grade->userid);

            if($this->is_user_not_student($userRoles, $studentArchetypes))
            {
                unset($grades[$key]);
            }
        }

        return $grades;
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

    private function get_ungraded_users_for_local_manager()
    {
        $sql = "SELECT gg.id, gg.itemid, gi.itemname, gi.itemmodule, gi.iteminstance, 
                       gg.userid, gg.usermodified, gi.courseid, c.fullname AS coursename, u.firstname 
        FROM {grade_grades} AS gg, {grade_items} AS gi, {course} AS c, {user} AS u
        WHERE gg.userid=gg.usermodified AND gg.finalgrade IS NULL # Select ungraded assign, quiz
            AND gg.itemid= gi.id AND gi.hidden=0 # Add itemname
            AND gg.userid=u.id
            AND gi.courseid=c.id AND gi.courseid IN(".$this->get_local_manager_course_in().") # Add course name
        ORDER BY c.shortname, gi.itemname";

        global $DB;
        return $DB->get_records_sql($sql, array());
    }

    private function get_local_manager_course_in()
    {
        $courses = $this->get_local_manager_courses();

        $str = '';
        $coursesCount = count($courses);
        for($i = 0; $i < $coursesCount; $i++)
        {
            $str.= $courses[$i];

            if(($i+1) < $coursesCount)
            {
                $str.= ', ';
            }
        }

        return $str;
    }

    private function get_local_manager_courses()
    {
        global $USER;

        $allCourses = nlib\get_user_courses($USER->id);
        $archetypeRoles = nlib\get_archetypes_roles(array('manager'));
    
        $managerCourses = array();
        foreach($allCourses as $courseid)
        {
            $userRoles = get_user_roles(\context_course::instance($courseid), $USER->id);
    
            if(nlib\is_user_have_role($archetypeRoles, $userRoles))
            {
                $managerCourses[] = $courseid;
            }
        }

        array_unique($managerCourses);
    
        return $managerCourses;
    }



}
