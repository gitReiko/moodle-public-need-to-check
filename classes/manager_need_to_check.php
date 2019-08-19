<?php

require_once 'data_types/item.php';
require_once 'data_types/teacher.php';

class ManagerNeedToCheck
{
    private $courses = array();

    const checkTime = 604800; // 7 days

    function __construct()
    {
        $ungradedGrades = $this->get_ungraded_grades();
        $this->parse_ungraded_grades($ungradedGrades);

        // Посчитать неоценённые работы для курсов и преподавателей
    }

    public function get_gui() : string 
    {
        print_r($this->courses);
        return '';
    }


    private function parse_ungraded_grades($ungradedGrades) 
    {
        foreach($ungradedGrades as $grade)
        {
            if($this->is_course_not_exist($grade->courseid))
            {
                $this->add_course_to_array($grade);
            }
            
            $teacher = new CheckingTeacher($grade); // !!! 
            if($this->is_teacher_not_exist($grade->courseid, $teacher->get_teacher_id($grade->courseid, $grade->userid, $grade->iteminstance)))
            {
                $this->add_teacher_to_array($grade);
            }

            if($this->is_item_not_exist($grade))
            {
                $this->add_item_to_array($grade);
            }
            else
            {
                $this->update_item($grade);
            }
        }
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
        $sql = 'SELECT gg.id, gg.itemid, gi.itemname, gi.itemmodule, gi.iteminstance, 
                       gg.userid, gg.usermodified, gi.courseid, c.fullname AS coursename, u.firstname 
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

    private function is_course_not_exist(int $courseid) : bool 
    {
        foreach($this->courses as $course)
        {
            if($course->id == $courseid) return false;
        }

        return true;
    }

    private function add_course_to_array(stdClass $grade) : void 
    {
        $course = new stdClass;
        $course->id = $grade->courseid;
        $course->name = $grade->coursename;
        $course->teachers = array();

        $this->courses[] = $course;
    }

    private function is_teacher_not_exist(int $courseid, int $teacherid) : bool 
    {
        foreach($this->courses as $course)
        {
            if($course->id == $courseid)
            {
                foreach($course->teachers as $teacher)
                {
                    if($teacher->get_id() == $teacherid) return false;
                }
            }
        }

        return true;
    }

    private function add_teacher_to_array(stdClass $grade) : void 
    {
        $teacher = new CheckingTeacher($grade);

        foreach($this->courses as $course)
        {
            if($course->id == $grade->courseid)
            {
                $course->teachers[] = $teacher;
                break;
            }
        }
    }

    private function is_item_not_exist(stdClass $grade) : bool 
    {
        foreach($this->courses as $course)
        {
            if($course->id == $grade->courseid)
            {
                foreach($course->teachers as $teacher)
                {
                    if($teacher->get_id() == $teacher->get_teacher_id($grade->courseid, $grade->userid, $grade->iteminstance))
                    {
                        foreach($teacher->get_items() as $item)
                        {
                            if($item->get_id() == $grade->itemid) return false;
                        }
                    }
                }
            }
        }

        return true;
    }

    private function add_item_to_array(stdClass $grade) : void 
    {
        $item = new checkedItem($grade);

        foreach($this->courses as $course)
        {
            if($course->id == $grade->courseid)
            {
                foreach($course->teachers as $teacher)
                {
                    if($teacher->get_id() == $teacher->get_teacher_id($grade->courseid, $grade->userid, $grade->iteminstance))
                    {
                        $teacher->add_item($item);
                        return;
                    }
                }
            }
        }
    }

    private function update_item(stdClass $grade) 
    {
        $item = $this->get_item($grade);
        $item->update_works_count($grade);
    }

    private function get_item(stdClass $grade)
    {
        foreach($this->courses as $course)
        {
            if($course->id == $grade->courseid)
            {
                foreach($course->teachers as $teacher)
                {
                    if($teacher->get_id() == $teacher->get_teacher_id($grade->courseid, $grade->userid, $grade->iteminstance))
                    {
                        foreach($teacher->get_items() as $item)
                        {
                            if($item->get_id() == $grade->itemid) return $item;
                        }
                    }
                }
            }
        }
    }



}

