<?php

use need_to_check_lib as nlib;

/**
 * This class manages the database block_need_to_check_teachers table.
 * 
 * block_need_to_check_teachers data base table is necessary for speed up the plugin.
 * 
 * @author Denis Makouski
 */
class DatabaseTeacherTableManager 
{
    private $teacherRoles;

    function __construct()
    {
        $this->teacherRoles = nlib\get_archetypes_roles(array('teacher', 'editingteacher'));
    }

    public function update_teachers_table()
    {
        $items = $this->get_ungraded_items();

        foreach($items as $item)
        {
            $this->update_item_teachers($item);
        }
    }

    private function get_ungraded_items()
    {
        $grades = new GlobalManagerGradeGradesGetter;
        return $grades->get_grades();
    }

    private function update_item_teachers(stdClass $item)
    {
        $teachers = $this->get_teachers($item);
        $this->add_missing_teachers_to_database($teachers, $item);
        $this->delete_irrelevant_teachers_from_database($teachers, $item);
    }

    private function get_teachers(stdClass $item) : array 
    {
        $cm = nlib\get_course_module($item);
        $users = $this->get_item_users($cm);
        
        $teachers = array();
        foreach($users as $user)
        {
            if(isset($user->id))
            {
                $userRoles = get_user_roles(\context_module::instance($cm->id), $user->id);

                if(nlib\is_user_have_role($this->teacherRoles, $userRoles))
                {
                    $teachers[] = $user->id;
                }
            }

        }

        // Because the same teacher can be in different groups
        $teachers = array_unique($teachers);

        return $teachers;
    }

    private function get_item_users($cm)
    {
        $groups = groups_get_activity_allowed_groups($cm);
        return nlib\get_groups_members($groups);
    }

    private function add_missing_teachers_to_database(array $teachers, stdClass $item)
    {
        foreach($teachers as $teacher)
        {
            if(!$this->is_teacher_exist($teacher, $item))
            {
                $this->add_teacher_to_database($teacher, $item);
            }
        }
    }

    private function is_teacher_exist(int $teacher, stdClass $item) : bool 
    {
        global $DB;
        $conditions = array('itemid'=>$item->itemid, 'teacherid'=>$teacher);

        if($DB->record_exists('block_need_to_check_teachers', $conditions))
        {
            return true;
        }
        else 
        {
            return false;
        }
    }

    private function add_teacher_to_database(int $teacher, stdClass $item)
    {
        global $DB;

        $row = new stdClass;
        $row->itemid = $item->itemid;
        $row->teacherid = $teacher;

        $DB->insert_record('block_need_to_check_teachers', $row, false);
    }

    private function delete_irrelevant_teachers_from_database(array $relevantTeachers, stdClass $item)
    {
        $itemTeachers = $this->get_item_teachers($item);

        foreach($itemTeachers as $iTeacher)
        {
            if(!$this->is_teacher_relevant($relevantTeachers, $iTeacher))
            {
                $this->delete_teacher_from_database($iTeacher, $item);
            }
        }
    }

    private function get_item_teachers(stdClass $item)
    {
        global $DB;
        $conditions = array('itemid'=>$item->itemid);
        $queries = $DB->get_records('block_need_to_check_teachers', $conditions);

        $teachers = array();
        foreach($queries as $query)
        {
            $teachers[] = $query->teacherid;
        }

        return $teachers;
    }

    private function is_teacher_relevant(array $relevantTeachers, int $teacher) : bool
    {
        foreach($relevantTeachers as $rTeacher)
        {
            if($rTeacher == $teacher)
            {
                return true;
            }
        }

        return false;
    }

    private function delete_teacher_from_database(int $teacher, stdClass $item)
    {
        global $DB;
        $conditions = array('itemid'=>$item->itemid, 'teacherid'=>$teacher);
        $DB->delete_records('block_need_to_check_teachers', $conditions);
    }

    

}

