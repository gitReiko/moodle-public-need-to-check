<?php


class ManagerNeedToCheck
{
    private $grades;

    function __construct()
    {
        $this->grades = $this->get_grades();
    }

    public function get_gui() : string 
    {
        return $this->get_items_list();
    }



    private function get_items_list() : string 
    {
        $course = 0;
        $str = '';

        foreach($this->grades as $grade)
        {
            if($grade->courseid != $course)
            {
                $course = $grade->courseid;
                $str.= "<h6>{$grade->coursename}</h6>";
            }

            $str.= "<p>{$grade->itemname} ({$grade->itemscount}) {$grade->firstname}</p>";
        }

        return $str;
    }

    private function get_grades()
    {
        $grades = $this->get_ungraded_users();
        $grades = $this->filter_out_all_non_students($grades);

        // Подсчитать работы и т.д.


        echo "count: " .count($grades);
        //print_r($grades);

        return $grades;
    }

    private function get_ungraded_users()
    {
        global $DB;
        // Группировка по itemid приводит к неправильным результатам (вероятно я не умею её использовать правильно)
        $sql = 'SELECT gg.id, gg.itemid, gi.itemname, gg.userid, gi.courseid, c.shortname AS coursename, u.firstname
        FROM {grade_grades} AS gg, {grade_items} AS gi, {course} AS c, {user} AS u
        WHERE gg.userid=gg.usermodified AND gg.finalgrade IS NULL # Select ungraded
            AND gg.itemid= gi.id AND gi.hidden=0 # Add itemname
            AND gg.userid=u.id
            AND gi.courseid=c.id # Add course name
        ORDER BY c.shortname, gi.itemname';

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

