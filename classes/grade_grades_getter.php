<?php

use need_to_check_lib as nlib;

abstract class GradeGradesGetter
{
    public function get_grades()
    {
        $grades = $this->get_ungraded_users();
        $grades = $this->filter_out_all_non_student_users($grades);
        return $grades;
    }

    abstract protected function get_ungraded_users();

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
}

/**
 * Returns grades for user with global role based on manager archetype.
 */
class GlobalManagerGradeGradesGetter extends GradeGradesGetter
{
    protected function get_ungraded_users()
    {
        $sql = "SELECT gg.id, gg.itemid, gi.itemname, gi.itemmodule, gi.iteminstance, 
                       gg.userid, gi.courseid, c.fullname AS coursename 
        FROM {grade_grades} AS gg, {grade_items} AS gi, {course} AS c, {user} AS u
        WHERE gg.userid=gg.usermodified AND gg.finalgrade IS NULL # Select ungraded assign, quiz
            AND gg.itemid= gi.id AND gi.hidden=0 # Add itemname
            AND gg.userid=u.id
            AND gi.courseid=c.id # Add course name
        ORDER BY c.shortname, gi.itemname";

        global $DB;
        return $DB->get_records_sql($sql, array());
    }
}

/**
 * Returns grades for user with role based on any archetype in the courses.
 */
abstract class LocalGradeGradesGetter extends GradeGradesGetter 
{
    protected $archetypeRoles;

    protected function get_ungraded_users()
    {
        $sql = "SELECT gg.id, gg.itemid, gi.itemname, gi.itemmodule, gi.iteminstance, 
                       gg.userid, gi.courseid, c.fullname AS coursename 
                FROM {grade_grades} AS gg, {grade_items} AS gi, {course} AS c, {user} AS u
                WHERE gg.userid=gg.usermodified AND gg.finalgrade IS NULL # Select ungraded assign, quiz
                    AND gg.itemid= gi.id AND gi.hidden=0 # Add itemname
                    AND gg.userid=u.id
                    AND gi.courseid=c.id # Add course name
                ORDER BY c.shortname, gi.itemname";

        global $DB;
        $grades = $DB->get_records_sql($sql, array());
        $grades = $this->filter_out_all_non_user_grades($grades);
        return $grades;
    }

    private function filter_out_all_non_user_grades($grades)
    {
        global $USER;

        $userGrades = array();
        foreach($grades as $grade)
        {
            $cmid = $this->get_course_module_id($grade);
            $userRoles = get_user_roles(\context_module::instance($cmid), $USER->id);

            if(nlib\is_user_have_role($this->archetypeRoles, $userRoles))
            {
                $userGrades[] = $grade;
            }

        }
        return $userGrades;
    }

    private function get_course_module_id($grade)
    {
        $sql = "SELECT cm.id
                FROM {course_modules} AS cm
                INNER JOIN {modules} AS m
                ON cm.module=m.id
                WHERE cm.instance = ? AND m.name=?";
        $conditions = array($grade->iteminstance, $grade->itemmodule);
        global $DB;

        $query = $DB->get_record_sql($sql, $conditions);

        if(isset($query->id)) return $query->id;
        else return null;
    }
}

/**
 * Returns grades for user with role based on manager archetype in the courses.
 */
class LocalManagerGradeGradesGetter extends LocalGradeGradesGetter
{
    function __construct() 
    {
        $this->archetypeRoles = nlib\get_archetypes_roles(array('manager'));
    }
}

/**
 * Returns grades for user with role based on teacher or editingteacher archetypes in the courses.
 */
class LocalTeacherGradeGradesGetter extends LocalGradeGradesGetter
{
    function __construct() 
    {
        $this->archetypeRoles = nlib\get_archetypes_roles(array('teacher', 'editingteacher'));
    }
}
